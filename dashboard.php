<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'quick_renew_service') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        if ($serviceId > 0) {
            $pdo->prepare("UPDATE services SET renewal_date = DATE_ADD(renewal_date, INTERVAL 1 YEAR) WHERE id = ?")->execute([$serviceId]);
            header('Location: dashboard.php?toast=Service renewed successfully');
        } else {
            header('Location: dashboard.php?toast=Invalid service ID&toast_type=error');
        }
        exit;
    }
}

$withinDays = 30;
$totalClients = (int) $pdo->query('SELECT COUNT(*) FROM clients WHERE deleted_at IS NULL')->fetchColumn();
$totalDomains = (int) $pdo->query("SELECT COUNT(*) FROM services WHERE service_type = 'domain' AND deleted_at IS NULL")->fetchColumn();
$totalHosting = (int) $pdo->query("SELECT COUNT(*) FROM services WHERE service_type = 'hosting' AND deleted_at IS NULL")->fetchColumn();
$upcomingDomainRenewals = (int) $pdo->query(
    "SELECT COUNT(*) FROM services 
     WHERE service_type = 'domain' 
       AND deleted_at IS NULL 
       AND DATE(renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$withinDays} DAY)"
)->fetchColumn();
$upcomingHostingRenewals = (int) $pdo->query(
    "SELECT COUNT(*) FROM services 
     WHERE service_type = 'hosting' 
       AND deleted_at IS NULL 
       AND DATE(renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$withinDays} DAY)"
)->fetchColumn();
$upcomingRenewals = (int) $pdo->query(
    'SELECT COUNT(*) 
     FROM services 
     WHERE deleted_at IS NULL
       AND DATE(renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)'
)->fetchColumn();
$overdueDomainRenewals = (int) $pdo->query(
    "SELECT COUNT(*) FROM services
     WHERE service_type = 'domain'
       AND deleted_at IS NULL
       AND DATE(renewal_date) < CURDATE()"
)->fetchColumn();
$overdueHostingRenewals = (int) $pdo->query(
    "SELECT COUNT(*) FROM services
     WHERE service_type = 'hosting'
       AND deleted_at IS NULL
       AND DATE(renewal_date) < CURDATE()"
)->fetchColumn();
$overdueRenewals = $overdueDomainRenewals + $overdueHostingRenewals;

$renewPerPage = 8;
$renewPage = max(1, (int) ($_GET['renew_page'] ?? 1));
$renewTotal = (int) $pdo->query(
    'SELECT COUNT(*)
     FROM services b
     JOIN clients c ON c.id = b.client_id
     WHERE b.deleted_at IS NULL
       AND c.deleted_at IS NULL
       AND DATE(b.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)'
)->fetchColumn();
$renewTotalPages = max(1, (int) ceil($renewTotal / $renewPerPage));
if ($renewPage > $renewTotalPages) {
    $renewPage = $renewTotalPages;
}
$renewOffset = ($renewPage - 1) * $renewPerPage;

$upcomingStmt = $pdo->prepare(
    'SELECT b.id AS service_id, b.renewal_date, b.service_type, b.name AS service_ref, b.amount, b.comment, c.name AS client_name, c.company AS client_company
     FROM services b
     JOIN clients c ON c.id = b.client_id
     WHERE b.deleted_at IS NULL
       AND c.deleted_at IS NULL
       AND DATE(b.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
     ORDER BY DATE(b.renewal_date) ASC
     LIMIT :limit OFFSET :offset'
);
$upcomingStmt->bindValue(':limit', $renewPerPage, PDO::PARAM_INT);
$upcomingStmt->bindValue(':offset', $renewOffset, PDO::PARAM_INT);
$upcomingStmt->execute();
$upcomingRows = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

$domainDetails = $pdo->query(
    "SELECT b.id AS service_id, c.name AS client_name, c.company AS client_company, b.name AS service_ref, b.renewal_date, b.amount, b.comment
     FROM services b
     JOIN clients c ON c.id = b.client_id
     WHERE b.deleted_at IS NULL
       AND c.deleted_at IS NULL
       AND b.service_type = 'domain'
       AND DATE(b.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
     ORDER BY DATE(b.renewal_date) ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$hostingDetails = $pdo->query(
    "SELECT b.id AS service_id, c.name AS client_name, c.company AS client_company, b.name AS service_ref, b.renewal_date, b.amount
     FROM services b
     JOIN clients c ON c.id = b.client_id
     WHERE b.deleted_at IS NULL
       AND c.deleted_at IS NULL
       AND b.service_type = 'hosting'
       AND DATE(b.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
     ORDER BY DATE(b.renewal_date) ASC"
)->fetchAll(PDO::FETCH_ASSOC);

$overdueDetails = $pdo->query(
    "SELECT b.id AS service_id,
            c.name AS client_name, 
            c.company AS client_company,
            b.service_type,
            b.name AS service_ref, 
            b.renewal_date, 
            b.amount, 
            b.comment
     FROM services b
     JOIN clients c ON c.id = b.client_id
     WHERE b.deleted_at IS NULL
       AND c.deleted_at IS NULL
       AND DATE(b.renewal_date) < CURDATE()
     ORDER BY DATE(b.renewal_date) ASC"
)->fetchAll(PDO::FETCH_ASSOC);;

$upcomingClientRenewals = (int) $pdo->query(
    "SELECT COUNT(*) FROM clients 
     WHERE deleted_at IS NULL 
       AND renewal_date IS NOT NULL 
       AND DATE(renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$withinDays} DAY)"
)->fetchColumn();

$clientRenewalDetails = $pdo->query(
    "SELECT name AS client_name, company AS client_company, renewal_date 
     FROM clients 
     WHERE deleted_at IS NULL 
       AND renewal_date IS NOT NULL 
       AND DATE(renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$withinDays} DAY)
     ORDER BY DATE(renewal_date) ASC"
)->fetchAll(PDO::FETCH_ASSOC);

renderLayoutStart('Dashboard', 'dashboard');
?>

<style>
    .clickable-card { cursor: pointer; }
    .clickable-card:hover { border-color: #c9d8ec; box-shadow: 0 8px 18px rgba(15, 43, 61, 0.08); }
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(2, 6, 23, 0.55);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .modal-card {
        width: 100%;
        max-width: 840px;
        max-height: 88vh;
        overflow: auto;
        background: #fff;
        border-radius: 22px;
        border: 1px solid #dbe4f1;
        padding: 18px;
    }
    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
</style>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-number"><?= $totalClients ?></div><div class="stat-label"><i class="fas fa-users"></i> Total Clients</div></div>
    <div class="stat-card"><div class="stat-number"><?= $totalDomains ?></div><div class="stat-label"><i class="fas fa-globe"></i> Total Domains</div></div>
    <div class="stat-card"><div class="stat-number"><?= $totalHosting ?></div><div class="stat-label"><i class="fas fa-server"></i> Total Hosting</div></div>
    <div class="stat-card clickable-card" data-open-modal="domainRenewalModal"><div class="stat-number"><?= $upcomingDomainRenewals ?></div><div class="stat-label">Upcoming Domain Renewals (30 Days)</div></div>
    <div class="stat-card clickable-card" data-open-modal="hostingRenewalModal"><div class="stat-number"><?= $upcomingHostingRenewals ?></div><div class="stat-label">Upcoming Hosting Renewals (30 Days)</div></div>
    <div class="stat-card clickable-card" data-open-modal="overdueRenewalModal"><div class="stat-number"><?= $overdueRenewals ?></div><div class="stat-label">Overdue Service Renewals</div></div>
</div>

<div class="card table-wrap">
    <div class="row-head">
        <h3><i class="fas fa-calendar-alt"></i> Upcoming Renewals (Next 30 Days)</h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Service Type</th>
                <th>Service</th>
                <th>Renewal Date</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$upcomingRows): ?>
                <tr><td colspan="5" class="inline-muted">No upcoming renewals.</td></tr>
            <?php else: ?>
                <?php foreach ($upcomingRows as $row): ?>
                    <tr>
                        <td>
                            <strong><?= esc($row['client_name']) ?></strong>
                            <?php if (!empty($row['client_company'])): ?>
                                <br><small class="inline-muted"><?= esc($row['client_company']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['service_type'] === 'domain' ? 'Domain' : 'Hosting' ?></td>
                        <td>
                            <?= esc($row['service_ref']) ?>
                        
                            <?php if ($row['service_type'] === 'domain'): ?>
                                <?php
                                    $isExpired = !empty($row['renewal_date']) && strtotime($row['renewal_date']) < time();
                                ?>
                                <br>
                                <small style="color:<?= $isExpired ? 'red' : 'green' ?>">
                                    <?= $isExpired ? 'Expired' : 'Active' ?>
                                </small>
                        
                                <?php if (!empty($row['comment'])): ?>
                                    <br>
                                    <small><?= esc($row['comment']) ?></small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= formatAppDate($row['renewal_date']) ?></td>
                        <td><?= number_format((float) $row['amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($renewTotalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $renewTotalPages; $i++): ?>
                <?php if ($i === $renewPage): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="dashboard.php?renew_page=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="overdueRenewalModal">
    <div class="modal-card">
        <div class="modal-head">
            <h3><i class="fas fa-triangle-exclamation"></i> Overdue Renewal Details</h3>
            <button type="button" class="btn-secondary" data-close-modal>Close</button>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Service</th>
                        <th style="">Renewal Date</th>
                        <th style="">Amount</th>
                        <!-- <th style="">Action</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$overdueDetails): ?>
                        <tr><td colspan="6" class="inline-muted">No overdue renewals.</td></tr>
                    <?php else: ?>
                        <?php foreach ($overdueDetails as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($item['client_name']) ?></strong>
                                    <?php if (!empty($item['client_company'])): ?>
                                        <br><small class="inline-muted"><?= esc($item['client_company']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['service_type'] === 'domain' ? 'Domain' : 'Hosting' ?></td>
                                <td>
                                    <?= esc($item['service_ref']) ?>
                                
                                    <?php if ($item['service_type'] === 'domain'): ?>
                                        <br><small style="color:red;">Expired</small>
                                
                                        <?php if (!empty($item['comment'])): ?>
                                            <br><small><?= esc($item['comment']) ?></small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatAppDate($item['renewal_date']) ?></td>
                                <td><?= number_format((float) $item['amount'], 2) ?></td>
                                <!-- <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="quick_renew_service">
                                        <input type="hidden" name="service_id" value="<?= (int) $item['service_id'] ?>">
                                        <button type="submit" class="btn-primary" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Mark as Paid and Renew for 1 Year?');">Paid & Renew</button>
                                    </form>
                                </td> -->
                            </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="domainRenewalModal">
    <div class="modal-card">
        <div class="modal-head">
            <h3><i class="fas fa-globe"></i> Upcoming Domain Renewals Details</h3>
            <button type="button" class="btn-secondary" data-close-modal>Close</button>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Domain</th>
                    <th>Renewal Date</th>
                    <th>Amount</th>
                    <!-- <th>Action</th> -->
                </tr>
            </thead>
            <tbody>
                <?php if (!$domainDetails): ?>
                    <tr><td colspan="5" class="inline-muted">No domain renewals in next 30 days.</td></tr>
                <?php else: ?>
                    <?php foreach ($domainDetails as $item): ?>
                        <tr>
                            <td>
                                <strong><?= esc($item['client_name']) ?></strong>
                                <?php if (!empty($item['client_company'])): ?>
                                    <br><small class="inline-muted"><?= esc($item['client_company']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($item['service_ref']) ?></td>
                            <td><?= formatAppDate($item['renewal_date']) ?></td>
                            <td><?= number_format((float) $item['amount'], 2) ?></td>
                            <!-- <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="quick_renew_service">
                                    <input type="hidden" name="service_id" value="<?= (int) $item['service_id'] ?>">
                                    <button type="submit" class="btn-primary" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Mark as Paid and Renew for 1 Year?');">Paid & Renew</button>
                                </form>
                            </td> -->
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="hostingRenewalModal">
    <div class="modal-card">
        <div class="modal-head">
            <h3><i class="fas fa-server"></i> Upcoming Hosting Renewals Details</h3>
            <button type="button" class="btn-secondary" data-close-modal>Close</button>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Hosting Service</th>
                        <th style="">Renewal Date</th>
                        <th style="">Amount</th>
                        <!-- <th style="">Action</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$hostingDetails): ?>
                        <tr><td colspan="5" class="inline-muted">No hosting renewals in next 30 days.</td></tr>
                    <?php else: ?>
                        <?php foreach ($hostingDetails as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($item['client_name']) ?></strong>
                                    <?php if (!empty($item['client_company'])): ?>
                                        <br><small class="inline-muted"><?= esc($item['client_company']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($item['service_ref']) ?></td>
                                <td><?= formatAppDate($item['renewal_date']) ?></td>
                                <td><?= number_format((float) $item['amount'], 2) ?></td>
                                <!-- <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="quick_renew_service">
                                        <input type="hidden" name="service_id" value="<?= (int) $item['service_id'] ?>">
                                        <button type="submit" class="btn-primary" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Mark as Paid and Renew for 1 Year?');">Paid & Renew</button>
                                    </form>
                                </td> -->
                            </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="clientRenewalModal">
    <div class="modal-card">
        <div class="modal-head">
            <h3><i class="fas fa-user-clock"></i> Upcoming Client Renewals Details</h3>
            <button type="button" class="btn-secondary" data-close-modal>Close</button>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Renewal Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$clientRenewalDetails): ?>
                    <tr><td colspan="2" class="inline-muted">No client renewals in next 30 days.</td></tr>
                <?php else: ?>
                    <?php foreach ($clientRenewalDetails as $item): ?>
                        <tr>
                            <td>
                                <strong><?= esc($item['client_name']) ?></strong>
                                <?php if (!empty($item['client_company'])): ?>
                                    <br><small class="inline-muted"><?= esc($item['client_company']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= formatAppDate($item['renewal_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    (function () {
        const openers = document.querySelectorAll('[data-open-modal]');
        const closers = document.querySelectorAll('[data-close-modal]');
        openers.forEach((opener) => {
            opener.addEventListener('click', function () {
                const modalId = this.getAttribute('data-open-modal');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                }
            });
        });
        closers.forEach((closer) => {
            closer.addEventListener('click', function () {
                const modal = this.closest('.modal-overlay');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });
        document.querySelectorAll('.modal-overlay').forEach((modal) => {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    })();
</script>

<?php renderLayoutEnd(); ?>

