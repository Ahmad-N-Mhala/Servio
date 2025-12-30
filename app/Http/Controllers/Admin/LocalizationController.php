<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

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
        ]);

        $file = $request->file;
        $key = $request->key;
        $value = $request->value;
        $lang = 'ar'; // Fixing to Arabic as per requirement

        $path = lang_path($lang . '/' . $file . '.php');

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
