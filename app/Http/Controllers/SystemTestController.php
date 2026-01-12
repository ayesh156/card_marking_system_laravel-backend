<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SystemTestController extends Controller
{
    /**
     * Simple API test with creative HTML response
     */
    public function simpleTest()
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ API Working</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }
        .container {
            text-align: center;
            padding: 60px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .checkmark {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #00c853, #64dd17);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            animation: pulse 2s infinite, bounce 0.6s ease-out;
            box-shadow: 0 10px 40px rgba(0, 200, 83, 0.4);
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 40px rgba(0, 200, 83, 0.4); }
            50% { box-shadow: 0 10px 60px rgba(0, 200, 83, 0.6); }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }
        h1 {
            font-size: 3em;
            background: linear-gradient(90deg, #00d4ff, #00ff88, #ff00ff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            animation: gradient 3s ease infinite;
        }
        @keyframes gradient {
            0%, 100% { background-position: 0% center; }
            50% { background-position: 100% center; }
        }
        .subtitle {
            color: #a0a0a0;
            font-size: 1.3em;
            margin-bottom: 30px;
        }
        .info-box {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .info-item:last-child { border-bottom: none; }
        .info-label { color: #888; }
        .info-value { color: #00d4ff; font-weight: bold; }
        .status-badge {
            display: inline-block;
            padding: 10px 30px;
            background: linear-gradient(90deg, #00c853, #64dd17);
            border-radius: 50px;
            color: #000;
            font-weight: bold;
            font-size: 1.1em;
            margin-top: 20px;
            animation: glow 2s infinite;
        }
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(0, 200, 83, 0.5); }
            50% { box-shadow: 0 0 40px rgba(0, 200, 83, 0.8); }
        }
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: -1;
        }
        .particle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(0, 212, 255, 0.3);
            border-radius: 50%;
            animation: float 15s infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="particles">
        <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 6s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 8s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 10s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 12s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 14s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 1s;"></div>
    </div>
    <div class="container">
        <div class="checkmark">✓</div>
        <h1>ZYNERGY API</h1>
        <p class="subtitle">Card Marking System Backend</p>
        <span class="status-badge">🚀 ONLINE & RUNNING</span>
        <div class="info-box">
            <div class="info-item">
                <span class="info-label">⏰ Server Time</span>
                <span class="info-value">' . Carbon::now()->format('Y-m-d H:i:s') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">🐘 PHP Version</span>
                <span class="info-value">' . PHP_VERSION . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">🔧 Laravel Version</span>
                <span class="info-value">' . app()->version() . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">🌍 Timezone</span>
                <span class="info-value">' . config('app.timezone') . '</span>
            </div>
        </div>
    </div>
</body>
</html>';

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Display the system test dashboard
     */
    public function dashboard()
    {
        return view('system-test');
    }

    /**
     * Run all system tests and return JSON
     */
    public function runTests()
    {
        $tests = [];
        $startTime = microtime(true);

        // 1. Database Connection Test
        $tests['database'] = $this->testDatabase();

        // 2. Tables Existence Test
        $tests['tables'] = $this->testTables();

        // 3. Data Counts Test
        $tests['data_counts'] = $this->testDataCounts();

        // 4. Grade 11 2025 Marking Deadline Test
        $tests['marking_deadline'] = $this->testMarkingDeadline();

        // 5. Group Classes Test
        $tests['group_classes'] = $this->testGroupClasses();

        // 6. API Response Time
        $tests['response_time'] = [
            'status' => 'pass',
            'message' => 'API responded in ' . round((microtime(true) - $startTime) * 1000, 2) . 'ms',
            'value' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
        ];

        // 7. PHP & Laravel Info
        $tests['environment'] = [
            'status' => 'pass',
            'message' => 'Environment info retrieved',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone')
        ];

        // Calculate overall status
        $failedTests = collect($tests)->filter(fn($t) => $t['status'] === 'fail')->count();
        $warningTests = collect($tests)->filter(fn($t) => $t['status'] === 'warning')->count();

        return response()->json([
            'overall_status' => $failedTests > 0 ? 'fail' : ($warningTests > 0 ? 'warning' : 'pass'),
            'summary' => [
                'total' => count($tests),
                'passed' => count($tests) - $failedTests - $warningTests,
                'warnings' => $warningTests,
                'failed' => $failedTests,
            ],
            'tests' => $tests,
            'timestamp' => Carbon::now()->toIso8601String()
        ]);
    }

    private function testDatabase()
    {
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            return [
                'status' => 'pass',
                'message' => "Connected to database: {$dbName}",
                'database' => $dbName
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    private function testTables()
    {
        $requiredTables = [
            'students', 'grades', 'tuitions', 'categories', 'classes', 
            'days', 'months', 'years', 'student_reports', 'students_has_tuitions',
            'tuitions_has_grades', 'users', 'migrations'
        ];

        $existingTables = [];
        $missingTables = [];

        foreach ($requiredTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $existingTables[] = $table;
            } else {
                $missingTables[] = $table;
            }
        }

        return [
            'status' => count($missingTables) === 0 ? 'pass' : 'fail',
            'message' => count($missingTables) === 0 
                ? 'All ' . count($requiredTables) . ' required tables exist'
                : 'Missing tables: ' . implode(', ', $missingTables),
            'existing' => count($existingTables),
            'missing' => $missingTables
        ];
    }

    private function testDataCounts()
    {
        try {
            $counts = [
                'students' => DB::table('students')->count(),
                'grades' => DB::table('grades')->count(),
                'tuitions' => DB::table('tuitions')->count(),
                'categories' => DB::table('categories')->count(),
                'classes' => DB::table('classes')->count(),
                'student_reports' => DB::table('student_reports')->count(),
                'enrollments' => DB::table('students_has_tuitions')->count(),
            ];

            return [
                'status' => 'pass',
                'message' => "Data counts retrieved successfully",
                'counts' => $counts
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => 'Failed to get data counts: ' . $e->getMessage()
            ];
        }
    }

    private function testMarkingDeadline()
    {
        try {
            $grade = DB::table('grades')
                ->where('grade_name', 'Grade 11 2025')
                ->first();

            if (!$grade) {
                return [
                    'status' => 'warning',
                    'message' => 'Grade 11 2025 not found in database'
                ];
            }

            $hasDeadline = !is_null($grade->marking_deadline ?? null);
            $deadline = $grade->marking_deadline ?? 'Not set';
            
            // Check if can still mark
            $canMark = $hasDeadline && Carbon::parse($grade->marking_deadline)->gte(Carbon::now());

            return [
                'status' => $hasDeadline ? 'pass' : 'warning',
                'message' => $hasDeadline 
                    ? "Grade 11 2025 marking deadline: {$deadline}" 
                    : 'Marking deadline not set for Grade 11 2025',
                'grade_id' => $grade->id,
                'grade_name' => $grade->grade_name,
                'marking_deadline' => $deadline,
                'can_mark' => $canMark,
                'days_remaining' => $hasDeadline ? Carbon::now()->diffInDays(Carbon::parse($grade->marking_deadline), false) : null
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => 'Marking deadline test failed: ' . $e->getMessage()
            ];
        }
    }

    private function testGroupClasses()
    {
        try {
            // Group category_id = 4, English class_id = 1
            $groupTuitions = DB::table('tuitions')
                ->where('category_id', 4)
                ->where('class_id', 1)
                ->get();

            $tuitionIds = $groupTuitions->pluck('id')->toArray();
            
            $studentCount = DB::table('students_has_tuitions')
                ->whereIn('tuition_id', $tuitionIds)
                ->count();

            $reportCount = DB::table('student_reports')
                ->whereIn('tuition_id', $tuitionIds)
                ->count();

            // Get grade mappings
            $gradeMappings = DB::table('tuitions_has_grades')
                ->whereIn('tuition_id', $tuitionIds)
                ->join('grades', 'tuitions_has_grades.grade_id', '=', 'grades.id')
                ->select('tuitions_has_grades.tuition_id', 'grades.grade_name')
                ->get()
                ->groupBy('tuition_id')
                ->map(fn($items) => $items->pluck('grade_name')->toArray());

            return [
                'status' => 'pass',
                'message' => "Found {$groupTuitions->count()} English Group tuitions",
                'tuition_count' => $groupTuitions->count(),
                'tuition_ids' => $tuitionIds,
                'student_enrollments' => $studentCount,
                'reports' => $reportCount,
                'grade_mappings' => $gradeMappings
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => 'Group classes test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Simple health check endpoint
     */
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'ZYNERGY Card Marking System API is running',
            'timestamp' => Carbon::now()->toIso8601String()
        ]);
    }
}
