#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const workspaceRoot = __dirname;

// Helper to flatten nested objects into dot notation
function getFlatKeys(obj, prefix = '') {
    let keys = {};
    if (!obj || typeof obj !== 'object') return keys;
    
    for (let key in obj) {
        if (obj.hasOwnProperty(key)) {
            const fullKey = prefix ? `${prefix}.${key}` : key;
            const val = obj[key];
            if (val && typeof val === 'object' && !Array.isArray(val)) {
                Object.assign(keys, getFlatKeys(val, fullKey));
            } else {
                keys[fullKey] = val;
            }
        }
    }
    return keys;
}

// Clean string check
function isEmpty(val) {
    if (val === null || val === undefined) return true;
    if (typeof val === 'string') {
        return val.trim() === '';
    }
    return false;
}

// Extract JSON string ignoring warnings/banners
function extractJson(stdout) {
    const startBracket = stdout.indexOf('{');
    const startArray = stdout.indexOf('[');
    let startIdx = -1;
    if (startBracket !== -1 && startArray !== -1) {
        startIdx = Math.min(startBracket, startArray);
    } else {
        startIdx = startBracket !== -1 ? startBracket : startArray;
    }
    
    if (startIdx === -1) {
        throw new Error("No JSON object or array found in output: " + stdout);
    }
    
    const endBracket = stdout.lastIndexOf('}');
    const endArray = stdout.lastIndexOf(']');
    const endIdx = Math.max(endBracket, endArray);
    
    if (endIdx === -1 || endIdx < startIdx) {
        throw new Error("No matching closing bracket found in output: " + stdout);
    }
    
    return stdout.substring(startIdx, endIdx + 1);
}

// Bidirectional deep merge of keys
// If key is missing or blank in target, populate with source value
function mergeObjects(source, target, pathTrace = '') {
    let changed = false;
    for (let key in source) {
        if (source.hasOwnProperty(key)) {
            const currentPath = pathTrace ? `${pathTrace}.${key}` : key;
            if (!(key in target) || target[key] === undefined) {
                // Key is missing, copy it
                target[key] = JSON.parse(JSON.stringify(source[key])); // deep copy
                console.log(`  ➕ Added key: [${currentPath}] -> value: ${JSON.stringify(source[key])}`);
                changed = true;
            } else if (typeof source[key] === 'object' && source[key] !== null && !Array.isArray(source[key])) {
                // If nested object
                if (typeof target[key] !== 'object' || target[key] === null || Array.isArray(target[key])) {
                    target[key] = {};
                }
                const subChanged = mergeObjects(source[key], target[key], currentPath);
                if (subChanged) changed = true;
            } else if (isEmpty(target[key]) && !isEmpty(source[key])) {
                // Key exists but is empty/blank, copy from source
                target[key] = source[key];
                console.log(`  ✏️ Updated empty key: [${currentPath}] -> value: ${JSON.stringify(source[key])}`);
                changed = true;
            }
        }
    }
    return changed;
}

// PHP string serializer
function serializePhpString(str) {
    if (str.includes('\n') || str.includes('\r') || str.includes('\t')) {
        const escaped = str
            .replace(/\\/g, '\\\\')
            .replace(/"/g, '\\"')
            .replace(/\$/g, '\\$')
            .replace(/\n/g, '\\n')
            .replace(/\r/g, '\\r')
            .replace(/\t/g, '\\t');
        return `"${escaped}"`;
    } else {
        const escaped = str
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'");
        return `'${escaped}'`;
    }
}

// PHP array serializer
function toPhpArrayString(obj, indent = 4) {
    const spaces = ' '.repeat(indent);
    const innerSpaces = ' '.repeat(indent + 4);
    
    if (obj === null || obj === undefined) {
        return 'null';
    }
    if (typeof obj === 'string') {
        return serializePhpString(obj);
    }
    if (typeof obj === 'number' || typeof obj === 'boolean') {
        return obj.toString();
    }
    if (Array.isArray(obj)) {
        const items = obj.map(item => toPhpArrayString(item, indent + 4));
        return `[\n${innerSpaces}${items.join(`,\n${innerSpaces}`)},\n${spaces}]`;
    }
    if (typeof obj === 'object') {
        const parts = [];
        for (let key in obj) {
            if (obj.hasOwnProperty(key)) {
                const escapedKey = key.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                const valStr = toPhpArrayString(obj[key], indent + 4);
                parts.push(`'${escapedKey}' => ${valStr}`);
            }
        }
        return `[\n${innerSpaces}${parts.join(`,\n${innerSpaces}`)},\n${spaces}]`;
    }
    return 'null';
}

// TS string serializer
function serializeTsString(str) {
    const escaped = str
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\n/g, '\\n')
        .replace(/\r/g, '\\r')
        .replace(/\t/g, '\\t');
    return `'${escaped}'`;
}

// TS object serializer
function toTsObjectString(obj, indent = 0) {
    const spaces = ' '.repeat(indent);
    const innerSpaces = ' '.repeat(indent + 4);
    
    if (obj === null || obj === undefined) {
        return 'null';
    }
    if (typeof obj === 'string') {
        return serializeTsString(obj);
    }
    if (typeof obj === 'number' || typeof obj === 'boolean') {
        return obj.toString();
    }
    if (Array.isArray(obj)) {
        const items = obj.map(item => toTsObjectString(item, indent + 4));
        return `[\n${innerSpaces}${items.join(`,\n${innerSpaces}`)},\n${spaces}]`;
    }
    if (typeof obj === 'object') {
        const parts = [];
        for (let key in obj) {
            if (obj.hasOwnProperty(key)) {
                const isIdentifier = /^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(key);
                const formattedKey = isIdentifier ? key : `'${key.replace(/'/g, "\\'")}'`;
                const valStr = toTsObjectString(obj[key], indent + 4);
                parts.push(`${formattedKey}: ${valStr},`);
            }
        }
        return `{\n${innerSpaces}${parts.join(`\n${innerSpaces}`)}\n${spaces}}`;
    }
    return 'null';
}

// Main sync function
function syncAll() {
    console.log('🔄 Starting translation synchronization script...');

    // --- Part 1: Backend PHP files ---
    console.log('\n--- Syncing Backend (PHP) Translations ---');
    const enDir = path.join(workspaceRoot, 'lang/en');
    const arDir = path.join(workspaceRoot, 'lang/ar');
    
    if (!fs.existsSync(enDir) || !fs.existsSync(arDir)) {
        console.error('❌ lang/en or lang/ar directory does not exist.');
        process.exit(1);
    }
    
    const enFiles = fs.readdirSync(enDir).filter(f => f.endsWith('.php'));
    
    enFiles.forEach(file => {
        const enPath = path.join(enDir, file);
        const arPath = path.join(arDir, file);
        
        if (!fs.existsSync(arPath)) {
            console.log(`📁 Arabic file ${file} is missing. Copying English file to start.`);
            fs.copyFileSync(enPath, arPath);
        }
        
        // Load contents
        let enData, arData;
        try {
            const escapedEnPath = enPath.replace(/'/g, "\\'");
            const stdout = execSync(`php -d display_errors=0 -d display_startup_errors=0 -d error_reporting=0 -r "echo json_encode(include '${escapedEnPath}');"`, { encoding: 'utf8' });
            enData = JSON.parse(extractJson(stdout));
        } catch (e) {
            console.error(`❌ Failed to parse ${enPath}:`, e.message);
            return;
        }
        
        try {
            const escapedArPath = arPath.replace(/'/g, "\\'");
            const stdout = execSync(`php -d display_errors=0 -d display_startup_errors=0 -d error_reporting=0 -r "echo json_encode(include '${escapedArPath}');"`, { encoding: 'utf8' });
            arData = JSON.parse(extractJson(stdout));
        } catch (e) {
            console.error(`❌ Failed to parse ${arPath}:`, e.message);
            return;
        }
        
        console.log(`📄 Auditing: ${file}`);
        
        // Bidirectional sync
        let enUpdated = mergeObjects(arData, enData, `EN:${file}`);
        let arUpdated = mergeObjects(enData, arData, `AR:${file}`);
        
        if (enUpdated) {
            const content = `<?php\n\nreturn ${toPhpArrayString(enData, 0)};\n`;
            fs.writeFileSync(enPath, content, 'utf8');
            console.log(`💾 Saved updates to ${enPath}`);
        }
        if (arUpdated) {
            const content = `<?php\n\nreturn ${toPhpArrayString(arData, 0)};\n`;
            fs.writeFileSync(arPath, content, 'utf8');
            console.log(`💾 Saved updates to ${arPath}`);
        }
    });

    // --- Part 2: Frontend TS files ---
    console.log('\n--- Syncing Frontend (TS) Translations ---');
    const enTsPath = path.join(workspaceRoot, 'resources/js/locales/en/index.ts');
    const arTsPath = path.join(workspaceRoot, 'resources/js/locales/ar/index.ts');
    
    if (!fs.existsSync(enTsPath) || !fs.existsSync(arTsPath)) {
        console.warn('⚠️  Frontend index.ts locale files not found. Skipping.');
        return;
    }
    
    const enTsContent = fs.readFileSync(enTsPath, 'utf8');
    const arTsContent = fs.readFileSync(arTsPath, 'utf8');
    
    // Convert ES6 export to CommonJS to evaluate
    const enJsContent = enTsContent.replace('export default {', 'module.exports = {');
    const arJsContent = arTsContent.replace('export default {', 'module.exports = {');
    
    const tempEnJs = path.join(workspaceRoot, 'temp_en_locale.cjs');
    const tempArJs = path.join(workspaceRoot, 'temp_ar_locale.cjs');
    
    fs.writeFileSync(tempEnJs, enJsContent, 'utf8');
    fs.writeFileSync(tempArJs, arJsContent, 'utf8');
    
    let enData = {}, arData = {};
    try {
        delete require.cache[require.resolve(tempEnJs)];
        enData = require(tempEnJs);
    } catch (e) {
        console.error('❌ Failed to load temp_en_locale.cjs:', e.message);
    }
    
    try {
        delete require.cache[require.resolve(tempArJs)];
        arData = require(tempArJs);
    } catch (e) {
        console.error('❌ Failed to load temp_ar_locale.cjs:', e.message);
    }
    
    // Clean up temporary files
    try {
        fs.unlinkSync(tempEnJs);
        fs.unlinkSync(tempArJs);
    } catch (e) {}
    
    console.log('📄 Auditing frontend index.ts files');
    
    let enUpdated = mergeObjects(arData, enData, 'EN:frontend');
    let arUpdated = mergeObjects(enData, arData, 'AR:frontend');
    
    if (enUpdated) {
        const content = `export default ${toTsObjectString(enData, 0)};\n`;
        fs.writeFileSync(enTsPath, content, 'utf8');
        console.log(`💾 Saved updates to ${enTsPath}`);
    }
    if (arUpdated) {
        const content = `export default ${toTsObjectString(arData, 0)};\n`;
        fs.writeFileSync(arTsPath, content, 'utf8');
        console.log(`💾 Saved updates to ${arTsPath}`);
    }
    
    console.log('\n✅ Translation synchronization complete!');
}

syncAll();
