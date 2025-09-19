<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Providers\DatabaseConnectionPoolServiceProvider;
use Exception;

class TestConnectionPool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test-pool {--connections=10 : Number of connections to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MySQL connection pool performance and configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Testing MySQL Connection Pool...');
        
        $connections = (int) $this->option('connections');
        
        // Test basic connection
        $this->testBasicConnection();
        
        // Test multiple connections
        $this->testMultipleConnections($connections);
        
        // Show connection pool info
        $this->showConnectionPoolInfo();
        
        // Test connection pool optimization
        $this->testConnectionPoolOptimization();
        
        $this->info('✅ Connection pool test completed!');
    }

    /**
     * Test basic database connection
     */
    private function testBasicConnection(): void
    {
        $this->info('📡 Testing basic database connection...');
        
        try {
            $startTime = microtime(true);
            $result = DB::select('SELECT 1 as test');
            $endTime = microtime(true);
            
            $this->info("✅ Basic connection successful");
            $this->info("⏱️  Query time: " . round(($endTime - $startTime) * 1000, 2) . "ms");
            $this->info("📊 Result: " . json_encode($result));
        } catch (Exception $e) {
            $this->error("❌ Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Test multiple connections
     */
    private function testMultipleConnections(int $count): void
    {
        $this->info("🔗 Testing {$count} concurrent connections...");
        
        $startTime = microtime(true);
        $successful = 0;
        $failed = 0;
        
        for ($i = 1; $i <= $count; $i++) {
            try {
                $result = DB::select('SELECT ? as connection_number, NOW() as current_time', [$i]);
                $successful++;
                
                if ($i <= 3) {
                    $this->line("  Connection {$i}: " . json_encode($result[0]));
                }
            } catch (Exception $e) {
                $failed++;
                $this->error("  Connection {$i} failed: " . $e->getMessage());
            }
        }
        
        $endTime = microtime(true);
        $totalTime = round(($endTime - $startTime) * 1000, 2);
        
        $this->info("📈 Results:");
        $this->info("  ✅ Successful: {$successful}");
        $this->info("  ❌ Failed: {$failed}");
        $this->info("  ⏱️  Total time: {$totalTime}ms");
        $this->info("  📊 Average time per connection: " . round($totalTime / $count, 2) . "ms");
    }

    /**
     * Show connection pool information
     */
    private function showConnectionPoolInfo(): void
    {
        $this->info('📊 Connection Pool Information:');
        
        try {
            $poolInfo = DatabaseConnectionPoolServiceProvider::getConnectionPoolInfo();
            
            foreach ($poolInfo as $name => $info) {
                $this->info("  Connection '{$name}':");
                $this->info("    Database: {$info['database_name']}");
                $this->info("    Driver: {$info['driver_name']}");
                $this->info("    Transaction Level: {$info['transaction_level']}");
                $this->info("    Connected: " . ($info['is_connected'] ? 'Yes' : 'No'));
            }
            
            // Show configuration
            $config = config('database.connections.mysql');
            if (isset($config['pool'])) {
                $this->info('  Pool Configuration:');
                $this->info("    Min Connections: {$config['pool']['min_connections']}");
                $this->info("    Max Connections: {$config['pool']['max_connections']}");
                $this->info("    Acquire Timeout: {$config['pool']['acquire_timeout']}s");
                $this->info("    Idle Timeout: {$config['pool']['idle_timeout']}s");
                $this->info("    Max Lifetime: {$config['pool']['max_lifetime']}s");
            }
        } catch (Exception $e) {
            $this->error('❌ Failed to get connection pool info: ' . $e->getMessage());
        }
    }

    /**
     * Test connection pool optimization
     */
    private function testConnectionPoolOptimization(): void
    {
        $this->info('🔧 Testing connection pool optimization...');
        
        try {
            $startTime = microtime(true);
            DatabaseConnectionPoolServiceProvider::optimizeConnectionPool();
            $endTime = microtime(true);
            
            $this->info('✅ Connection pool optimized');
            $this->info('⏱️  Optimization time: ' . round(($endTime - $startTime) * 1000, 2) . 'ms');
            
            // Test connection after optimization
            $result = DB::select('SELECT "optimized" as status, NOW() as timestamp');
            $this->info('📊 Post-optimization test: ' . json_encode($result[0]));
            
        } catch (Exception $e) {
            $this->error('❌ Optimization failed: ' . $e->getMessage());
        }
    }
}
