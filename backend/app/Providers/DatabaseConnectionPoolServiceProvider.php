<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseConnectionPoolServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Cấu hình connection pool cho MySQL
        $this->configureConnectionPool();
        
        // Monitor connection pool
        $this->monitorConnectionPool();
    }

    /**
     * Cấu hình connection pool
     */
    private function configureConnectionPool(): void
    {
        // Lắng nghe sự kiện khi connection được tạo
        DB::listen(function ($query) {
            if (config('app.debug')) {
                Log::info('Database Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                    'connection' => $query->connectionName
                ]);
            }
        });

        // Cấu hình connection pool cho MySQL
        $this->app->resolving('db', function (DatabaseManager $db) {
            $db->extend('mysql_pool', function ($config, $name) {
                $config['driver'] = 'mysql';
                
                // Thêm các tùy chọn connection pool
                $poolConfig = $config['pool'] ?? [];
                
                $config['options'] = array_merge($config['options'] ?? [], [
                    \PDO::ATTR_PERSISTENT => $config['persistent'] ?? true,
                    \PDO::ATTR_TIMEOUT => $config['timeout'] ?? 30,
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]);

                return $this->createConnection($config, $name, $poolConfig);
            });
        });
    }

    /**
     * Tạo connection với pool configuration
     */
    private function createConnection(array $config, string $name, array $poolConfig): Connection
    {
        $connection = DB::connection($name);
        
        // Thiết lập các thông số pool
        if (!empty($poolConfig)) {
            $this->applyPoolSettings($connection, $poolConfig);
        }

        return $connection;
    }

    /**
     * Áp dụng các thiết lập pool cho connection
     */
    private function applyPoolSettings(Connection $connection, array $poolConfig): void
    {
        $pdo = $connection->getPdo();
        
        // Thiết lập timeout cho connection
        if (isset($poolConfig['timeout'])) {
            $pdo->setAttribute(\PDO::ATTR_TIMEOUT, $poolConfig['timeout']);
        }

        // Thiết lập persistent connection
        if (isset($poolConfig['persistent']) && $poolConfig['persistent']) {
            $pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);
        }

        Log::info('Connection Pool Settings Applied', [
            'connection' => $connection->getName(),
            'pool_config' => $poolConfig
        ]);
    }

    /**
     * Monitor connection pool performance
     */
    private function monitorConnectionPool(): void
    {
        // Chỉ monitor trong môi trường development
        if (config('app.env') === 'local' && config('app.debug')) {
            $this->app->terminating(function () {
                $connections = DB::getConnections();
                
                foreach ($connections as $name => $connection) {
                    Log::info('Connection Pool Status', [
                        'connection_name' => $name,
                        'database_name' => $connection->getDatabaseName(),
                        'driver_name' => $connection->getDriverName(),
                        'transaction_level' => $connection->transactionLevel(),
                    ]);
                }
            });
        }
    }

    /**
     * Tối ưu hóa connection pool
     */
    public static function optimizeConnectionPool(): void
    {
        // Đóng các connection không sử dụng
        DB::purge();
        
        // Reconnect với cấu hình mới
        DB::reconnect();
        
        Log::info('Connection pool optimized');
    }

    /**
     * Lấy thông tin connection pool
     */
    public static function getConnectionPoolInfo(): array
    {
        $connections = DB::getConnections();
        $info = [];

        foreach ($connections as $name => $connection) {
            $info[$name] = [
                'database_name' => $connection->getDatabaseName(),
                'driver_name' => $connection->getDriverName(),
                'transaction_level' => $connection->transactionLevel(),
                'is_connected' => $connection->getPdo() !== null,
            ];
        }

        return $info;
    }
}
