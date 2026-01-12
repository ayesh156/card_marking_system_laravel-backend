<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 ZYNERGY System Test Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            color: #fff;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header h1 {
            font-size: 2.5em;
            background: linear-gradient(90deg, #00d4ff, #7b2cbf, #e94560);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .header p {
            color: #a0a0a0;
            font-size: 1.1em;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: bold;
            margin: 15px 0;
            font-size: 1.2em;
        }

        .status-pass { background: linear-gradient(90deg, #00c853, #64dd17); color: #000; }
        .status-warning { background: linear-gradient(90deg, #ff9800, #ffc107); color: #000; }
        .status-fail { background: linear-gradient(90deg, #f44336, #e91e63); color: #fff; }
        .status-loading { background: linear-gradient(90deg, #2196f3, #03a9f4); color: #fff; }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.08);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .summary-card .number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .summary-card.passed .number { color: #00e676; }
        .summary-card.warnings .number { color: #ffeb3b; }
        .summary-card.failed .number { color: #ff5252; }
        .summary-card.total .number { color: #00d4ff; }

        .test-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .test-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .test-header:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .test-header h3 {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .test-icon {
            font-size: 1.5em;
        }

        .test-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .test-status.pass { background: rgba(0, 230, 118, 0.2); color: #00e676; border: 1px solid #00e676; }
        .test-status.warning { background: rgba(255, 235, 59, 0.2); color: #ffeb3b; border: 1px solid #ffeb3b; }
        .test-status.fail { background: rgba(255, 82, 82, 0.2); color: #ff5252; border: 1px solid #ff5252; }

        .test-details {
            padding: 0 20px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .test-message {
            color: #b0b0b0;
            margin: 15px 0;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .data-item {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 10px;
        }

        .data-item .label {
            color: #888;
            font-size: 0.85em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .data-item .value {
            font-size: 1.3em;
            font-weight: bold;
            color: #00d4ff;
        }

        .btn {
            background: linear-gradient(90deg, #00d4ff, #7b2cbf);
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            color: #fff;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 20px 0;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .timestamp {
            text-align: center;
            color: #666;
            margin-top: 30px;
        }

        .loading {
            text-align: center;
            padding: 50px;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top-color: #00d4ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .api-info {
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            font-family: monospace;
        }

        .api-info code {
            background: rgba(0, 212, 255, 0.2);
            padding: 3px 8px;
            border-radius: 5px;
            color: #00d4ff;
        }

        .copy-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            margin-left: 10px;
            font-size: 0.9em;
        }

        .copy-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 ZYNERGY System Test</h1>
            <p>Card Marking System - Backend Health Check Dashboard</p>
            <div id="overall-status" class="status-badge status-loading pulse">
                ⏳ Ready to Test
            </div>
            <br>
            <button class="btn" id="run-btn" onclick="runTests()">
                🚀 Run System Tests
            </button>
        </div>

        <div id="summary-section" style="display: none;">
            <div class="summary-cards">
                <div class="summary-card total">
                    <div class="number" id="total-count">0</div>
                    <div>Total Tests</div>
                </div>
                <div class="summary-card passed">
                    <div class="number" id="passed-count">0</div>
                    <div>Passed</div>
                </div>
                <div class="summary-card warnings">
                    <div class="number" id="warning-count">0</div>
                    <div>Warnings</div>
                </div>
                <div class="summary-card failed">
                    <div class="number" id="failed-count">0</div>
                    <div>Failed</div>
                </div>
            </div>
        </div>

        <div id="tests-container">
            <!-- Tests will be rendered here -->
        </div>

        <div class="api-info">
            <strong>📡 API Endpoints:</strong><br><br>
            <div style="margin: 10px 0;">
                <strong>Health Check:</strong> 
                <code id="health-url">{{ url('/api/system/health') }}</code>
                <button class="copy-btn" onclick="copyToClipboard('health-url')">📋 Copy</button>
            </div>
            <div style="margin: 10px 0;">
                <strong>Full Test (JSON):</strong> 
                <code id="test-url">{{ url('/api/system/test') }}</code>
                <button class="copy-btn" onclick="copyToClipboard('test-url')">📋 Copy</button>
            </div>
            <div style="margin: 10px 0;">
                <strong>Dashboard UI:</strong> 
                <code id="dashboard-url">{{ url('/api/system/dashboard') }}</code>
                <button class="copy-btn" onclick="copyToClipboard('dashboard-url')">📋 Copy</button>
            </div>
        </div>

        <div class="timestamp" id="timestamp"></div>
    </div>

    <script>
        const testIcons = {
            database: '🗄️',
            tables: '📋',
            data_counts: '📊',
            marking_deadline: '📅',
            group_classes: '👥',
            response_time: '⚡',
            environment: '🖥️'
        };

        const testNames = {
            database: 'Database Connection',
            tables: 'Required Tables',
            data_counts: 'Data Counts',
            marking_deadline: 'Grade 11 2025 Marking Deadline',
            group_classes: 'English Group Classes',
            response_time: 'API Response Time',
            environment: 'Environment Info'
        };

        async function runTests() {
            const btn = document.getElementById('run-btn');
            const statusBadge = document.getElementById('overall-status');
            const container = document.getElementById('tests-container');
            const summarySection = document.getElementById('summary-section');

            btn.disabled = true;
            btn.textContent = '⏳ Running Tests...';
            statusBadge.className = 'status-badge status-loading pulse';
            statusBadge.textContent = '⏳ Running Tests...';
            container.innerHTML = '<div class="loading"><div class="spinner"></div><p>Executing system tests...</p></div>';

            try {
                const response = await fetch('/api/system/test');
                const data = await response.json();

                // Update overall status
                statusBadge.className = `status-badge status-${data.overall_status}`;
                statusBadge.textContent = data.overall_status === 'pass' 
                    ? '✅ All Tests Passed' 
                    : data.overall_status === 'warning'
                        ? '⚠️ Passed with Warnings'
                        : '❌ Some Tests Failed';

                // Update summary
                summarySection.style.display = 'block';
                document.getElementById('total-count').textContent = data.summary.total;
                document.getElementById('passed-count').textContent = data.summary.passed;
                document.getElementById('warning-count').textContent = data.summary.warnings;
                document.getElementById('failed-count').textContent = data.summary.failed;

                // Render tests
                container.innerHTML = '';
                for (const [key, test] of Object.entries(data.tests)) {
                    container.innerHTML += renderTest(key, test);
                }

                // Update timestamp
                document.getElementById('timestamp').textContent = `Last tested: ${new Date(data.timestamp).toLocaleString()}`;

            } catch (error) {
                statusBadge.className = 'status-badge status-fail';
                statusBadge.textContent = '❌ Connection Failed';
                container.innerHTML = `
                    <div class="test-section">
                        <div class="test-header">
                            <h3><span class="test-icon">❌</span> Connection Error</h3>
                            <span class="test-status fail">FAIL</span>
                        </div>
                        <div class="test-details">
                            <p class="test-message">${error.message}</p>
                        </div>
                    </div>
                `;
            } finally {
                btn.disabled = false;
                btn.textContent = '🔄 Run Tests Again';
            }
        }

        function renderTest(key, test) {
            const icon = testIcons[key] || '🔍';
            const name = testNames[key] || key;
            
            let detailsHtml = `<p class="test-message">${test.message}</p>`;

            // Add specific details based on test type
            if (key === 'data_counts' && test.counts) {
                detailsHtml += '<div class="data-grid">';
                for (const [k, v] of Object.entries(test.counts)) {
                    detailsHtml += `
                        <div class="data-item">
                            <div class="label">${k.replace('_', ' ')}</div>
                            <div class="value">${v.toLocaleString()}</div>
                        </div>
                    `;
                }
                detailsHtml += '</div>';
            }

            if (key === 'marking_deadline' && test.status === 'pass') {
                detailsHtml += `
                    <div class="data-grid">
                        <div class="data-item">
                            <div class="label">Grade</div>
                            <div class="value">${test.grade_name}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Deadline</div>
                            <div class="value">${test.marking_deadline}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Can Mark</div>
                            <div class="value">${test.can_mark ? '✅ Yes' : '❌ No'}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Days Remaining</div>
                            <div class="value">${test.days_remaining || 'N/A'}</div>
                        </div>
                    </div>
                `;
            }

            if (key === 'group_classes') {
                detailsHtml += `
                    <div class="data-grid">
                        <div class="data-item">
                            <div class="label">Tuitions</div>
                            <div class="value">${test.tuition_count}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Students</div>
                            <div class="value">${test.student_enrollments}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Reports</div>
                            <div class="value">${test.reports}</div>
                        </div>
                    </div>
                `;
            }

            if (key === 'environment') {
                detailsHtml += `
                    <div class="data-grid">
                        <div class="data-item">
                            <div class="label">PHP Version</div>
                            <div class="value">${test.php_version}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Laravel Version</div>
                            <div class="value">${test.laravel_version}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Server Time</div>
                            <div class="value">${test.server_time}</div>
                        </div>
                        <div class="data-item">
                            <div class="label">Timezone</div>
                            <div class="value">${test.timezone}</div>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="test-section">
                    <div class="test-header">
                        <h3><span class="test-icon">${icon}</span> ${name}</h3>
                        <span class="test-status ${test.status}">${test.status.toUpperCase()}</span>
                    </div>
                    <div class="test-details">
                        ${detailsHtml}
                    </div>
                </div>
            `;
        }

        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied: ' + text);
            });
        }
    </script>
</body>
</html>
