<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class LocalizationController extends Controller
{
    public function index(Request $request)
    {
        $langPath = lang_path();
        $files = File::files($langPath . '/en');

        $translations = [];

        foreach ($files as $file) {
            $fileName = $file->getFilenameWithoutExtension();
            $enData = include $file->getPathname();

            $arPath = $langPath . '/ar/' . $fileName . '.php';
            $arData = File::exists($arPath) ? include $arPath : [];

            // Flatten generic function
            $flatten = function ($array, $prefix = '') use (&$flatten) {
                $result = [];
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $result = $result + $flatten($value, $prefix . $key . '.');
                    } else {
                        $result[$prefix . $key] = $value;
                    }
                }
                return $result;
            };

            $flatEn = $flatten($enData);
            $flatAr = $flatten($arData);

            foreach ($flatEn as $key => $value) {
                // Filter by search if exists
                if ($request->search) {
                    if (
                        strpos(strtolower($key), strtolower($request->search)) === false &&
                        strpos(strtolower((string) $value), strtolower($request->search)) === false &&
                        strpos(strtolower((string) ($flatAr[$key] ?? '')), strtolower($request->search)) === false
                    ) {
                        continue;
                    }
                }

                $translations[] = [
                    'file' => $fileName,
                    'key' => $key,
                    'full_key' => $fileName . '.' . $key,
                    'en' => $value,
                    'ar' => $flatAr[$key] ?? '',
                ];
            }
        }

        // Pagination manually since it's array
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = count($translations);
        $sliced = array_slice($translations, ($page - 1) * $perPage, $perPage);

        return Inertia::render('Admin/Localization/Index', [
            'translations' => [
                'data' => $sliced,
                'total' => $total,
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
            'key' => 'required|string',
            'value' => 'nullable|string',
            'lang' => 'required|string|in:en,ar',
        ]);

        $file = $request->file;
        $key = $request->key;
        $value = $request->value;
        $lang = $request->lang;
        $path = lang_path($lang . '/' . $file . '.php');

        // Ensure directory exists
        if (!File::exists(dirname($path))) {
            File::makeDirectory(dirname($path), 0755, true);
        }

        // Load existing
        $data = File::exists($path) ? include $path : [];
        if (!is_array($data))
            $data = [];

        // Set value using dot notation helper
        \Illuminate\Support\Arr::set($data, $key, $value);

        // Save back
        $content = "<?php\n\nreturn " . $this->varExport($data) . ";\n";
        File::put($path, $content);

        return redirect()->back()->with('success', 'Translation updated successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'en_value' => 'required|string',
            'ar_value' => 'nullable|string',
        ]);

        $fullKey = $request->key;
        $parts = explode('.', $fullKey, 2);

        if (count($parts) === 2) {
            $file = $parts[0];
            $key = $parts[1];
        } else {
            // Default to 'common' if no dot notation provided
            $file = 'common';
            $key = $fullKey;
        }

        // Check for duplicates in English file (primary source)
        $path = lang_path('en/' . $file . '.php');
        $data = File::exists($path) ? include $path : [];

        if (Arr::has($data, $key)) {
            return redirect()->back()->withErrors(['key' => "The key '{$fullKey}' already exists in {$file}.php"]);
        }

        // Save English
        $this->saveTranslation('en', $file, $key, $request->en_value);

        // Save Arabic (if provided, or create empty entry)
        if ($request->ar_value) {
            $this->saveTranslation('ar', $file, $key, $request->ar_value);
        }

        return redirect()->back()->with('success', 'Translation created successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $file = $request->file('file');

        // Simple CSV parser for now
        $handle = fopen($file->getRealPath(), "r");
        $header = fgetcsv($handle, 1000, ","); // Skip header: key, en, ar

        // Basic validation for header structure could be added here

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Assuming order: key, en, ar
            $fullKey = $data[0] ?? null;
            $enVal = $data[1] ?? null;
            $arVal = $data[2] ?? null;

            if (!$fullKey || !$enVal)
                continue;

            $parts = explode('.', $fullKey, 2);
            if (count($parts) === 2) {
                $fileKey = $parts[0];
                $innerKey = $parts[1];
            } else {
                $fileKey = 'common';
                $innerKey = $fullKey;
            }

            // Save English
            $this->saveTranslation('en', $fileKey, $innerKey, $enVal);

            // Save Arabic if present
            if ($arVal) {
                $this->saveTranslation('ar', $fileKey, $innerKey, $arVal);
            }
        }
        fclose($handle);

        return redirect()->back()->with('success', 'Translations imported successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $fullKey = $request->key;
        $parts = explode('.', $fullKey, 2);

        if (count($parts) === 2) {
            $file = $parts[0];
            $key = $parts[1];
        } else {
            $file = 'common';
            $key = $fullKey;
        }

        // Check for usage in Plans (basic check)
        $isUsed = \App\Models\Plan::where('name', $key)
            ->orWhere('description', $key)
            ->orWhere('name', $fullKey) // Check full key too
            ->orWhere('description', $fullKey)
            ->exists();

        if ($isUsed) {
            return redirect()->back()->with('error', 'This translation key is currently used by a Subscription Plan. Please update the plan first.');
        }

        // Delete from English
        $this->deleteKey('en', $file, $key);

        // Delete from Arabic
        $this->deleteKey('ar', $file, $key);

        return redirect()->back()->with('success', 'Translation deleted successfully.');
    }

    private function deleteKey($lang, $file, $key)
    {
        $path = lang_path($lang . '/' . $file . '.php');

        if (File::exists($path)) {
            $data = include $path;
            if (is_array($data) && Arr::has($data, $key)) {
                Arr::forget($data, $key);

                $content = "<?php\n\nreturn " . $this->varExport($data) . ";\n";
                File::put($path, $content);

                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($path, true);
                }
            }
        }
    }

    private function saveTranslation($lang, $file, $key, $value)
    {
        $path = lang_path($lang . '/' . $file . '.php');

        // Ensure directory exists
        if (!File::exists(dirname($path))) {
            File::makeDirectory(dirname($path), 0755, true);
        }

        // Always load fresh data to avoid race conditions or stale data overwrites
        $data = File::exists($path) ? include $path : [];
        if (!is_array($data))
            $data = [];

        Arr::set($data, $key, $value);

        $content = "<?php\n\nreturn " . $this->varExport($data) . ";\n";
        File::put($path, $content);

        // Clear OPCache if enabled to reflect changes immediately
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }

    // Helper to format array output nicer than var_export
    private function varExport($expression)
    {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [NULL, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        return $export;
    }
}
