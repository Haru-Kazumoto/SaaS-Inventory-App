<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings by scanning all classes in
     * app/Repositories and binding any App\Interfaces\* interface
     * they implement.
     */
    public function register(): void
    {
        $baseNamespace = 'App';

        // DUAKAN: path + namespace untuk interface
        $interfaceBases = [
            ['path' => app_path('Interfaces'),                'ns' => $baseNamespace . '\\Interfaces'],
            ['path' => app_path('Repositories/Interfaces'),   'ns' => $baseNamespace . '\\Repositories\\Interfaces'],
        ];

        $repositoryBasePath = app_path('Repositories');
        $repositories = $this->getClassMap($repositoryBasePath, $baseNamespace . '\\Repositories');

        // Kumpulkan semua interface dari kedua basis
        $allInterfaces = [];
        foreach ($interfaceBases as $ib) {
            if (is_dir($ib['path'])) {
                $allInterfaces = array_merge($allInterfaces, $this->getClassMap($ib['path'], $ib['ns']));
            }
        }

        // Bind: untuk setiap repo, bind semua interface yang diimplement ke repo tsb
        foreach ($repositories as $repoClass) {
            if (!class_exists($repoClass)) {
                continue;
            }
            $ref = new ReflectionClass($repoClass);
            if ($ref->isAbstract() || $ref->isInterface()) {
                continue;
            }

            foreach ($ref->getInterfaceNames() as $iface) {
                // hanya bind interface yang berada pada basis yang kita dukung
                $isSupported = Str::startsWith($iface, $baseNamespace . '\\Interfaces\\')
                    || Str::startsWith($iface, $baseNamespace . '\\Repositories\\Interfaces\\');

                if ($isSupported && in_array($iface, $allInterfaces, true)) {
                    $this->app->bind($iface, $repoClass);
                }
            }
        }
    }

    protected function getClassMap(string $dir, string $namespace): array
    {
        if (!is_dir($dir)) return [];

        $classes = [];
        $directoryIterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($directoryIterator);
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $fileInfo) {
            $filePath = $fileInfo[0];
            $relativePath = Str::after($filePath, $dir . DIRECTORY_SEPARATOR);
            $className = $namespace . '\\' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relativePath);
            $classes[] = $className;
        }

        return $classes;
    }
}
