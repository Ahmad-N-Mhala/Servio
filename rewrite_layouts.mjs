import fs from 'fs';
import glob from 'glob';

const files = glob.sync('resources/js/Pages/**/*.vue');

let updatedFiles = 0;

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    // Check if the file imports MainLayout or AdminLayout
    const usesMainLayout = content.includes('import MainLayout from');
    const usesAdminLayout = content.includes('import AdminLayout from');
    
    if (!usesMainLayout && !usesAdminLayout) return;
    
    const layoutAlias = usesMainLayout ? 'MainLayout' : 'AdminLayout';
    
    // 1. Remove the import statement
    content = content.replace(new RegExp(`import ${layoutAlias} from '[^']+';\n?`), '');
    content = content.replace(new RegExp(`import ${layoutAlias} from "[^"]+";\n?`), '');
    
    // 2. We need to add `defineOptions({ layout: MainLayout })` to script setup
    // But since imports are removed, we must re-import it properly
    // Let's add the import and defineOptions
    const layoutImportStr = `import ${layoutAlias} from '@/Layouts/${layoutAlias}.vue';\n`;
    const defineOptionsStr = `defineOptions({ layout: ${layoutAlias} });\n`;
    
    // Inject right after <script setup...>
    content = content.replace(/<script\s+setup[^>]*>/i, `$& \n${layoutImportStr}${defineOptionsStr}`);
    
    // 3. Remove <MainLayout ...> or <AdminLayout ...>
    // Note: It might have multiline attributes or the > might be on a diff line
    content = content.replace(new RegExp(`<${layoutAlias}[^>]*>`, 'g'), '');
    
    // 4. Remove </MainLayout> or </AdminLayout>
    content = content.replace(new RegExp(`</${layoutAlias}>`, 'g'), '');

    fs.writeFileSync(file, content, 'utf8');
    updatedFiles++;
});

console.log(`Updated ${updatedFiles} files.`);
