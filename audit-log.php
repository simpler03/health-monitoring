<?php
/**
 * Audit Log Page
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireRole('admin');

require_once __DIR__ . '/config/Database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_old_logs'])) {
    try {
        $database = new Database();
        $db = $database->connect();

        // Get logs older than 30 days
        $query = "SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id WHERE a.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY a.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $old_logs = $stmt->fetchAll();

        if (empty($old_logs)) {
            $message = "No logs older than 30 days found.";
            $message_type = 'info';
        } else {
            $filename = 'audit_logs_older_than_30_days_' . date('Ymd_His') . '.doc';
            header('Content-Type: application/msword; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0, must-revalidate');

            echo '<!DOCTYPE html>' . "\n";
            echo '<html>' . "\n";
            echo '<head><meta charset="UTF-8"><title>Audit Logs Older Than 30 Days</title><style>' . "\n";
            echo '@page { size: 14in 8.5in; margin: 0.5in; mso-page-orientation: landscape; }' . "\n";
            echo 'body { font-family: Arial, sans-serif; margin: 0; padding: 0.75in; color: #222; }' . "\n";
            echo 'h1, h2 { margin: 0 0 0.5em 0; }' . "\n";
            echo 'table { width: 100%; border-collapse: collapse; margin-top: 1em; }' . "\n";
            echo 'th, td { border: 1px solid #666; padding: 8px; vertical-align: top; }' . "\n";
            echo 'th { background: #f1f1f1; font-weight: bold; text-align: left; }' . "\n";
            echo 'tr:nth-child(even) td { background: #fafafa; }' . "\n";
            echo '.meta { margin-top: 0.5em; font-size: 0.95em; color: #444; }' . "\n";
            echo '</style></head><body>' . "\n";
            echo '<h1>Audit Log Export</h1>' . "\n";
            echo '<div class="meta">Export date: ' . date('M d, Y h:i:s A') . '</div>' . "\n";
            echo '<div class="meta">Export includes only audit logs older than 30 days.</div>' . "\n";
            echo '<table>' . "\n";
            echo '<thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th></tr></thead>' . "\n";
            echo '<tbody>' . "\n";

            foreach ($old_logs as $log) {
                echo '<tr>' . "\n";
                echo '<td>' . htmlspecialchars(date('M d, Y h:i:s A', strtotime($log['created_at']))) . '</td>' . "\n";
                echo '<td>' . htmlspecialchars($log['full_name'] ?? 'System') . '</td>' . "\n";
                echo '<td>' . htmlspecialchars($log['action']) . '</td>' . "\n";
                echo '<td>' . nl2br(htmlspecialchars($log['new_value'] ?? $log['old_value'] ?? '')) . '</td>' . "\n";
                echo '</tr>' . "\n";
            }

            echo '</tbody></table>' . "\n";
            echo '</body></html>' . "\n";

            $delete_query = "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->execute();
            exit;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$action_filter = isset($_GET['action_filter']) ? trim($_GET['action_filter']) : '';
$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    $database = new Database();
    $db = $database->connect();
    
    // Build query
    $query = "SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id";
    $count_query = "SELECT COUNT(*) FROM audit_logs a";
    
    if ($action_filter) {
        $query .= " WHERE a.action = ?";
        $count_query .= " WHERE a.action = ?";
    }
    
    $query .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    if ($action_filter) {
        $count_stmt->bindValue(1, $action_filter);
    }
    $count_stmt->execute();
    $total_logs = $count_stmt->fetchColumn();
    $total_pages = ceil($total_logs / $per_page);
    
    // Get logs
    $stmt = $db->prepare($query);
    if ($action_filter) {
        $stmt->bindValue(1, $action_filter);
        $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    } else {
        $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    if ($is_ajax) {
        // Return JSON for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'logs' => $logs,
            'total_pages' => $total_pages,
            'current_page' => $page
        ]);
        exit;
    }
} catch (Exception $e) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
    $logs = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - Health Performance Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; color: #333; }
        .section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .back-link { color: #667eea; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        .filters { display: flex; gap: 20px; align-items: center; margin-bottom: 20px; }
        .filters label { font-weight: 600; color: #333; }
        .filters select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .pagination { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px; }
        .pagination button, .pagination span { padding: 8px 12px; border: 1px solid #ddd; background: white; cursor: pointer; border-radius: 4px; }
        .pagination .active { background: #667eea; color: white; border-color: #667eea; }
        .pagination .disabled { background: #f5f5f5; color: #999; cursor: not-allowed; }
        .jump-input { display: flex; align-items: center; gap: 5px; }
        .jump-input input { width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="container">
                <a class="back-link" onclick="window.location.href='dashboard.php'">← Back</a>
        <div class="header">
            <h1>Audit Log</h1>
            <form method="post">
                <button type="submit" name="clear_old_logs" value="1" 
                        onclick="return confirm('Are you sure you want to clear and export all audit logs older than 30 days? The file will be downloaded automatically.')"
                        style="background:#dc3545;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">
                    Clear 30+ Days Old Logs (Export Word)
                </button>
            </form>
        </div>
        <?php if ($message): ?>
            <div style="background:<?php 
                if ($message_type === 'success') echo '#e8f5e9';
                elseif ($message_type === 'error') echo '#ffebee';
                else echo '#e3f2fd';
            ?>;color:<?php 
                if ($message_type === 'success') echo '#2e7d32';
                elseif ($message_type === 'error') echo '#c62828';
                else echo '#1976d2';
            ?>;padding:12px 16px;border-radius:4px;margin-bottom:20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <div class="section">
            <div class="filters">
                <label for="action-filter">Filter by Action:</label>
                <select id="action-filter">
                    <option value="">All Actions</option>
                    <option value="LOGIN" <?php echo $action_filter === 'LOGIN' ? 'selected' : ''; ?>>LOGIN</option>
                    <option value="LOGOUT" <?php echo $action_filter === 'LOGOUT' ? 'selected' : ''; ?>>LOGOUT</option>
                    <option value="CREATE_USER" <?php echo $action_filter === 'CREATE_USER' ? 'selected' : ''; ?>>CREATE_USER</option>
                    <option value="UPDATE_USER" <?php echo $action_filter === 'UPDATE_USER' ? 'selected' : ''; ?>>UPDATE_USER</option>
                    <option value="CREATE_FACILITY" <?php echo $action_filter === 'CREATE_FACILITY' ? 'selected' : ''; ?>>CREATE_FACILITY</option>
                    <option value="UPDATE_FACILITY" <?php echo $action_filter === 'UPDATE_FACILITY' ? 'selected' : ''; ?>>UPDATE_FACILITY</option>
                    <option value="CREATE_EVALUATION" <?php echo $action_filter === 'CREATE_EVALUATION' ? 'selected' : ''; ?>>CREATE_EVALUATION</option>
                    <option value="UPDATE_EVALUATION" <?php echo $action_filter === 'UPDATE_EVALUATION' ? 'selected' : ''; ?>>UPDATE_EVALUATION</option>
                </select>
            </div>
            <table id="audit-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="audit-tbody">
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i:s A', strtotime($log['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($log['new_value'] ?? $log['old_value'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination" id="pagination">
                <!-- Pagination will be generated by JS -->
            </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = <?php echo $page; ?>;
        let currentFilter = '<?php echo addslashes($action_filter); ?>';
        let totalPages = <?php echo $total_pages; ?>;

        function loadData(page = 1, filter = '') {
            fetch(`?page=${page}&action_filter=${encodeURIComponent(filter)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                currentPage = data.current_page;
                totalPages = data.total_pages;
                updateTable(data.logs);
                updatePagination();
            })
            .catch(error => {
                console.error('Error loading data:', error);
            });
        }

        function updateTable(logs) {
            const tbody = document.getElementById('audit-tbody');
            tbody.innerHTML = logs.map(log => `
                <tr>
                    <td>${new Date(log.created_at).toLocaleString('en-US', { 
                        month: 'short', day: '2-digit', year: 'numeric', 
                        hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true 
                    })}</td>
                    <td>${(log.full_name || 'System').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</td>
                    <td>${log.action.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</td>
                    <td>${(log.new_value || log.old_value || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>')}</td>
                </tr>
            `).join('');
        }

        function updatePagination() {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '<<';
            prevBtn.classList.toggle('disabled', currentPage === 1);
            prevBtn.onclick = () => currentPage > 1 && loadData(currentPage - 1, currentFilter);
            pagination.appendChild(prevBtn);

            // Page numbers
            const pages = generatePageNumbers(currentPage, totalPages);
            pages.forEach(page => {
                if (page === '...') {
                    const span = document.createElement('span');
                    span.textContent = '...';
                    pagination.appendChild(span);
                } else {
                    const btn = document.createElement('button');
                    btn.textContent = page;
                    btn.classList.toggle('active', page === currentPage);
                    btn.onclick = () => loadData(page, currentFilter);
                    pagination.appendChild(btn);
                }
            });

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.textContent = '>>';
            nextBtn.classList.toggle('disabled', currentPage === totalPages);
            nextBtn.onclick = () => currentPage < totalPages && loadData(currentPage + 1, currentFilter);
            pagination.appendChild(nextBtn);

            // Jump input
            const jumpDiv = document.createElement('div');
            jumpDiv.className = 'jump-input';
            jumpDiv.innerHTML = `
                <span>Go to:</span>
                <input type="number" id="jump-page" min="1" max="${totalPages}" value="${currentPage}">
            `;
            pagination.appendChild(jumpDiv);

            // Jump input event
            document.getElementById('jump-page').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const page = parseInt(this.value);
                    if (page >= 1 && page <= totalPages) {
                        loadData(page, currentFilter);
                    }
                }
            });
        }

        function generatePageNumbers(current, total) {
            const pages = [];
            const maxVisible = 7;
            
            if (total <= maxVisible) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                pages.push(1);
                
                let start = Math.max(2, current - 2);
                let end = Math.min(total - 1, current + 2);
                
                if (start > 2) {
                    pages.push('...');
                }
                
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                
                if (end < total - 1) {
                    pages.push('...');
                }
                
                if (total > 1) {
                    pages.push(total);
                }
            }
            
            return pages;
        }

        // Initial pagination
        updatePagination();

        // Filter change event
        document.getElementById('action-filter').addEventListener('change', function() {
            currentFilter = this.value;
            loadData(1, currentFilter); // Force to page 1
        });
    </script>
</body>
</html>
