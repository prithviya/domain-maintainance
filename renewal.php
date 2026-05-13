<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$pdo = db();

// Filter inputs
$filterClientName = trim((string) ($_GET['filter_client_name'] ?? ''));
$filterServiceType = (string) ($_GET['filter_service_type'] ?? '');
$filterDaysRange = (string) ($_GET['filter_days_range'] ?? '90');

// Validate filter inputs
if (!in_array($filterServiceType, ['', 'domain', 'hosting'], true)) {
    $filterServiceType = '';
}
if (!in_array($filterDaysRange, ['30', '60', '90'], true)) {
    $filterDaysRange = '90';
}

// Build WHERE clause
$whereClause = 'c.deleted_at IS NULL AND s.deleted_at IS NULL AND s.renewal_date IS NOT NULL AND s.renewal_date >= CURDATE() AND s.renewal_date <= DATE_ADD(CURDATE(), INTERVAL ' . (int)$filterDaysRange . ' DAY)';
$params = [];

if ($filterClientName !== '') {
    $whereClause .= ' AND (c.name LIKE ? OR c.company LIKE ?)';
    $params[] = '%' . $filterClientName . '%';
    $params[] = '%' . $filterClientName . '%';
}

if ($filterServiceType !== '') {
    $whereClause .= ' AND s.service_type = ?';
    $params[] = $filterServiceType;
}

// Pagination
$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));

// Get total count
$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM services s
     JOIN clients c ON c.id = s.client_id
     WHERE {$whereClause}"
);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Get renewal list
$queryParams = $params;
$stmt = $pdo->prepare(
    "SELECT
        c.id AS client_id,
        c.name AS client_name,
        c.company AS client_company,
        c.email AS client_email,
        c.phone AS client_phone,
        s.id AS service_id,
        s.service_type,
        s.name AS service_name,
        s.renewal_date,
        s.amount,
        s.ownership_type,
        s.comment,
        DATEDIFF(s.renewal_date, NOW()) AS days_until
     FROM services s
     JOIN clients c ON c.id = s.client_id
     WHERE {$whereClause}
     ORDER BY s.renewal_date ASC, c.name ASC
     LIMIT ? OFFSET ?"
);
$queryParams[] = $perPage;
$queryParams[] = $offset;
$stmt->execute($queryParams);
$renewals = $stmt->fetchAll(PDO::FETCH_ASSOC);

function redirectClients(string $message = '', string $type = 'success'): void
{
    $query = [];
    if ($message !== '') {
        $query['toast'] = $message;
        $query['toast_type'] = $type;
    }
    $url = 'clients.php';
    if ($query) {
        $url .= '?' . http_build_query($query);
    }
    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_client') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $name = trim((string) ($_POST['name'] ?? ''));
        $company = trim((string) ($_POST['company'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $renewalDate = trim((string) ($_POST['renewal_date'] ?? ''));
        $status = ($_POST['status'] ?? 'Active') === 'Disabled' ? 'Disabled' : 'Active';

        if ($name !== '' && $phone !== '') {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE clients SET name = ?, company = ?, email = ?, phone = ?, renewal_date = ?, status = ? WHERE id = ? AND deleted_at IS NULL');
                $stmt->execute([$name, $company, $email, $phone, $renewalDate ?: null, $status, $id]);
                redirectClients('Client updated successfully');
            } else {
                $stmt = $pdo->prepare('INSERT INTO clients (name, company, email, phone, renewal_date, status) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $company, $email, $phone, $renewalDate ?: null, $status]);
                redirectClients('Client added successfully');
            }
        }
        redirectClients('Client name and phone are required', 'error');
    }

   if ($action === 'add_service') {
        $clientId = (int) ($_POST['client_id'] ?? 0);
        $serviceEntries = $_POST['services'] ?? [];
        $insertedCount = 0;
    
        if ($clientId > 0) {
            $stmt = $pdo->prepare(
                'INSERT INTO services 
                (client_id, service_type, name, renewal_date, amount, ownership_type, comment)
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
    
            foreach ($serviceEntries as $entry) {
                $name = trim((string) ($entry['name'] ?? ''));
                $renewalDate = trim((string) ($entry['renewal_date'] ?? ''));
                $amount = (float) ($entry['amount'] ?? 0);
                $ownershipType = ($entry['ownership_type'] ?? 'client') === 'our' ? 'our' : 'client';
                $comment = trim((string) ($entry['comment'] ?? ''));
    
                $types = $entry['types'] ?? [];
    
                if ($name === '' || empty($types)) {
                    continue;
                }
    
                foreach ($types as $type) {
                    if (!in_array($type, ['domain', 'hosting'])) continue;
    
                    $stmt->execute([
                        $clientId,
                        $type,
                        $name,
                        $renewalDate ?: null,
                        $amount,
                        $ownershipType,
                        $comment ?: null
                    ]);
    
                    $insertedCount++;
                }
            }
        }
    
        if ($insertedCount > 0) {
            redirectClients('Service added successfully');
        }
    
        redirectClients('Service name and type required', 'error');
    }

    if ($action === 'update_service') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $serviceType = ($_POST['service_type'] ?? 'domain') === 'hosting' ? 'hosting' : 'domain';
        $name = trim((string) ($_POST['name'] ?? ''));
        $renewalDate = trim((string) ($_POST['renewal_date'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $ownershipType = ($_POST['ownership_type'] ?? 'client') === 'our' ? 'our' : 'client';
        $comment = trim((string) ($_POST['comment'] ?? ''));

        if ($serviceId > 0 && $name !== '') {
            $stmt = $pdo->prepare(
                'UPDATE services
                 SET service_type=?, name=?, renewal_date=?, amount=?, ownership_type=?, comment=?
                 WHERE id=? AND deleted_at IS NULL'
            );

            $stmt->execute([
                $serviceType,
                $name,
                $renewalDate ?: null,
                $amount,
                $ownershipType,
                $comment ?: null,
                $serviceId
            ]);

            redirectClients('Service updated successfully');
        }

        redirectClients('Service name required', 'error');
    }
}
if (isset($_GET['delete_service'])) {
    $id = (int) $_GET['delete_service'];
    $stmt = $pdo->prepare('UPDATE services SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    redirectClients('Service deleted successfully');
}

$filterClientName = trim((string) ($_GET['filter_client_name'] ?? ''));
$filterServiceType = (string) ($_GET['filter_service_type'] ?? '');
$filterRenewalMonth = trim((string) ($_GET['filter_renewal_month'] ?? ''));

if (!in_array($filterServiceType, ['', 'domain', 'hosting'], true)) {
    $filterServiceType = '';
}
if ($filterRenewalMonth !== '' && !preg_match('/^\d{4}-\d{2}$/', $filterRenewalMonth)) {
    $filterRenewalMonth = '';
}

$serviceFilterSql = 'c.deleted_at IS NULL AND s.deleted_at IS NULL';
$serviceFilterParams = [];

if ($filterClientName !== '') {
    $serviceFilterSql .= ' AND (c.name LIKE :client_name OR c.company LIKE :client_company)';
    $serviceFilterParams[':client_name'] = '%' . $filterClientName . '%';
    $serviceFilterParams[':client_company'] = '%' . $filterClientName . '%';
}

if ($filterServiceType !== '') {
    $serviceFilterSql .= ' AND s.service_type = :service_type';
    $serviceFilterParams[':service_type'] = $filterServiceType;
}

if ($filterRenewalMonth !== '') {
    $serviceFilterSql .= ' AND DATE_FORMAT(s.renewal_date, "%Y-%m") = :renewal_month';
    $serviceFilterParams[':renewal_month'] = $filterRenewalMonth;
}

$serviceStmt = $pdo->prepare('SELECT * FROM services WHERE client_id = ? AND deleted_at IS NULL ORDER BY id DESC');

// Get upcoming renewals (next 90 days)
$upcomingStmt = $pdo->query(
    "SELECT
        c.id AS client_id,
        c.name AS client_name,
        c.company AS client_company,
        c.email AS client_email,
        c.phone AS client_phone,
        s.id AS service_id,
        s.service_type,
        s.name AS service_name,
        s.renewal_date,
        s.amount,
        s.ownership_type,
        DATEDIFF(s.renewal_date, NOW()) AS days_until
     FROM services s
     JOIN clients c ON c.id = s.client_id
     WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL
     AND s.renewal_date IS NOT NULL
     AND s.renewal_date >= CURDATE()
     AND s.renewal_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
     ORDER BY s.renewal_date ASC, c.name ASC"
);
$upcomingRenewals = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

$clientSummaryGroupsStmt = $pdo->query(
    "SELECT c.id, c.name, c.company, COUNT(s.id) AS service_count
     FROM clients c
     LEFT JOIN services s ON s.client_id = c.id AND s.deleted_at IS NULL
     WHERE c.deleted_at IS NULL
     GROUP BY c.id
     ORDER BY c.name ASC"
);
$clientSummaryGroups = $clientSummaryGroupsStmt->fetchAll(PDO::FETCH_ASSOC);

renderLayoutStart('Renewals', 'renewal', 'Service Renewal Management');
?>

<style>
    .filter-group { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px; }
    .filter-item { flex: 1; min-width: 220px; }
    .stat-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; text-align: center; margin-bottom: 16px; }
    .stat-value { font-size: 2rem; font-weight: 700; color: #0f172a; }
    .stat-label { font-size: 0.85rem; color: #64748b; margin-top: 4px; text-transform: uppercase; }
</style>

<div class="card" style="margin-bottom: 20px;">
    <div class="row-head">
        <h3><i class="fas fa-calendar-check"></i> Renewal Statistics</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
        <?php
        $stats30 = (int) $pdo->query(
            "SELECT COUNT(*) FROM services s JOIN clients c ON c.id = s.client_id
             WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL
             AND DATE(s.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"
        )->fetchColumn();
        $stats60 = (int) $pdo->query(
            "SELECT COUNT(*) FROM services s JOIN clients c ON c.id = s.client_id
             WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL
             AND DATE(s.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)"
        )->fetchColumn();
        $stats90 = (int) $pdo->query(
            "SELECT COUNT(*) FROM services s JOIN clients c ON c.id = s.client_id
             WHERE c.deleted_at IS NULL AND s.deleted_at IS NULL
             AND DATE(s.renewal_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)"
        )->fetchColumn();
        ?>
        <div class="stat-box">
            <div class="stat-value"><?= $stats30 ?></div>
            <div class="stat-label">Next 30 Days</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $stats60 ?></div>
            <div class="stat-label">Next 60 Days</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $stats90 ?></div>
            <div class="stat-label">Next 90 Days</div>
        </div>
        <div class="stat-box">
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-label">Current Filter</div>
        </div>
    </div>
</div>

<div class="card table-wrap">
    <div class="row-head">
        <h3><i class="fas fa-search"></i> Filter Renewals</h3>
    </div>
    <form method="get" style="margin-bottom: 16px;">
        <div class="filter-group form-group">
            <div class="filter-item">
                <label>Client Name</label>
                <input type="text" name="filter_client_name" value="<?= esc($filterClientName) ?>" placeholder="Search by name/company">
            </div>
            <div class="filter-item">
                <label>Service Type</label>
                <select name="filter_service_type">
                    <option value="">All Services</option>
                    <option value="domain" <?= $filterServiceType === 'domain' ? 'selected' : '' ?>>Domain</option>
                    <option value="hosting" <?= $filterServiceType === 'hosting' ? 'selected' : '' ?>>Hosting</option>
                </select>
            </div>
            <div class="filter-item">
                <label>Days Range</label>
                <select name="filter_days_range">
                    <option value="30" <?= $filterDaysRange === '30' ? 'selected' : '' ?>>Next 30 Days</option>
                    <option value="60" <?= $filterDaysRange === '60' ? 'selected' : '' ?>>Next 60 Days</option>
                    <option value="90" <?= $filterDaysRange === '90' ? 'selected' : '' ?>>Next 90 Days (Default)</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px; padding-bottom: 2px;">
                <button class="btn-primary" type="submit"><i class="fas fa-filter"></i> Apply Filter</button>
                <a class="btn-secondary" href="renewal.php">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="card table-wrap">
    <div class="row-head">
        <h3><i class="fas fa-list"></i> Renewal List</h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Service</th>
                <th>Type</th>
                <th>Renewal Date</th>
                <th>Days Until</th>
                <th>Amount</th>
                <th>Ownership</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$renewals): ?>
                <tr><td colspan="7" class="inline-muted">No renewals found.</td></tr>
            <?php else: ?>
                <?php foreach ($renewals as $renewal): ?>
                    <tr>
                        <td>
                            <strong><?= esc($renewal['client_name']) ?></strong>
                            <?php if ($renewal['client_company']): ?>
                                <br><span class="inline-muted"><?= esc($renewal['client_company']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($renewal['service_name']) ?></td>
                        <td>
                            <span class="badge <?= $renewal['service_type'] === 'domain' ? 'badge-info' : 'badge-success' ?>">
                                <?= $renewal['service_type'] === 'domain' ? 'Domain' : 'Hosting' ?>
                            </span>
                        </td>
                        <td><strong><?= formatAppDate($renewal['renewal_date']) ?></strong></td>
                        <td>
                            <?php
                                $daysUntil = (int) $renewal['days_until'];
                                if ($daysUntil <= 7) {
                                    echo '<span class="inline-danger"><strong>' . $daysUntil . ' days</strong></span>';
                                } elseif ($daysUntil <= 30) {
                                    echo '<span class="inline-warning">' . $daysUntil . ' days</span>';
                                } else {
                                    echo '<span class="inline-muted">' . $daysUntil . ' days</span>';
                                }
                            ?>
                        </td>
                        <td>$<?= number_format((float) $renewal['amount'], 2) ?></td>
                        <td>
                            <span class="badge <?= $renewal['ownership_type'] === 'our' ? 'badge-primary' : 'badge-secondary' ?>">
                                <?= $renewal['ownership_type'] === 'our' ? 'Our Side' : 'Client' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="renewal.php?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">« First</a>
                <a href="renewal.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">‹ Previous</a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            if ($startPage > 1): ?>
                <span class="disabled">...</span>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="renewal.php?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
                <span class="disabled">...</span>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="renewal.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next ›</a>
                <a href="renewal.php?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">Last »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        (function () {
            const modal = document.getElementById('editClientModal');
            const closeBtn = document.getElementById('closeEditModal');
            const openButtons = document.querySelectorAll('.open-edit-modal');
            const serviceModal = document.getElementById('editServiceModal');
            const closeServiceBtn = document.getElementById('closeServiceEditModal');
            const serviceOpenButtons = document.querySelectorAll('.open-service-edit-modal');
            const addServiceModal = document.getElementById('addServiceModal');
            const closeAddServiceBtn = document.getElementById('closeAddServiceModal');
            const addServiceButtons = document.querySelectorAll('.open-add-service-modal');
            const serviceEntryContainer = document.getElementById('serviceEntryContainer');
            const addOtherServiceBtn = document.getElementById('addOtherServiceBtn');
            const clientDetailsModal = document.getElementById('clientDetailsModal');
            const closeClientDetailsBtn = document.getElementById('closeClientDetailsModal');
            const clientDetailsName = document.getElementById('clientDetailsName');
            const clientDetailsCompany = document.getElementById('clientDetailsCompany');
            const clientDetailsEmail = document.getElementById('clientDetailsEmail');
            const clientDetailsPhone = document.getElementById('clientDetailsPhone');
            const clientDetailsServicesBody = document.getElementById('clientDetailsServicesBody');
            const clientGroupButtons = document.querySelectorAll('.open-client-details-modal');
            const clientGroups = <?= json_encode($clientGroups, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
            let serviceEntryIndex = 0;
            let activeServiceRow = null;

        function openModal(data) {
            document.getElementById('editClientId').value = data.id || '';
            document.getElementById('editClientName').value = data.name || '';
            document.getElementById('editClientCompany').value = data.company || '';
            document.getElementById('editClientEmail').value = data.email || '';
            document.getElementById('editClientPhone').value = data.phone || '';
            document.getElementById('editClientRenewalDate').value = data.renewalDate || '';
            document.getElementById('editClientStatus').value = data.status || 'Active';
            modal.style.display = 'flex';
            // Focus management for accessibility
            modal.setAttribute('aria-hidden', 'false');
            document.getElementById('editClientName').focus();
        }

        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }

        openButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                openModal({
                    id: this.dataset.id,
                    name: this.dataset.name,
                    company: this.dataset.company,
                    email: this.dataset.email,
                    phone: this.dataset.phone,
                    renewalDate: this.dataset.renewalDate,
                    status: this.dataset.status
                });
            });
        });

        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Keyboard support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });

        function setServiceRowBlur(row) {
            document.querySelectorAll('tbody tr').forEach((tr) => {
                tr.classList.toggle('blur-background', row && tr !== row);
                tr.classList.toggle('active-row', row && tr === row);
            });
            activeServiceRow = row;
        }

        function openServiceModal(data, row) {
            document.getElementById('editServiceId').value = data.id || '';
            document.getElementById('editServiceClientName').textContent = data.clientName || '';
            document.getElementById('editServiceClientCompany').textContent = data.clientCompany || '';
            document.getElementById('editServiceName').value = data.name || '';
            document.getElementById('editServiceComment').value = data.comment || '';
            document.getElementById('editServiceOwnershipType').value = data.ownershipType || 'client';
            
            // Set service type radio button
            const currentType = data.type || 'domain';
            if (currentType === 'domain') {
                document.getElementById('editServiceTypeDomain').checked = true;
                document.getElementById('editServiceDomainRenewalDate').style.display = '';
                document.getElementById('editServiceHostingRenewalDate').style.display = 'none';
                document.getElementById('editServiceDomainAmount').style.display = '';
                document.getElementById('editServiceHostingAmount').style.display = 'none';
                document.getElementById('editServiceDomainRenewalDate').value = data.renewalDate || '';
                document.getElementById('editServiceDomainAmount').value = data.amount || '';
            } else {
                document.getElementById('editServiceTypeHosting').checked = true;
                document.getElementById('editServiceDomainRenewalDate').style.display = 'none';
                document.getElementById('editServiceHostingRenewalDate').style.display = '';
                document.getElementById('editServiceDomainAmount').style.display = 'none';
                document.getElementById('editServiceHostingAmount').style.display = '';
                document.getElementById('editServiceHostingRenewalDate').value = data.renewalDate || '';
                document.getElementById('editServiceHostingAmount').value = data.amount || '';
            }
            
            setServiceRowBlur(row || null);
            serviceModal.style.display = 'flex';
            serviceModal.setAttribute('aria-hidden', 'false');
            document.getElementById('editServiceTypeDomain').focus();
        }
        
        // Handle service type radio change
        document.getElementById('editServiceTypeDomain').addEventListener('change', function() {
            document.getElementById('editServiceDomainRenewalDate').style.display = '';
            document.getElementById('editServiceHostingRenewalDate').style.display = 'none';
            document.getElementById('editServiceDomainAmount').style.display = '';
            document.getElementById('editServiceHostingAmount').style.display = 'none';
        });
        
        document.getElementById('editServiceTypeHosting').addEventListener('change', function() {
            document.getElementById('editServiceDomainRenewalDate').style.display = 'none';
            document.getElementById('editServiceHostingRenewalDate').style.display = '';
            document.getElementById('editServiceDomainAmount').style.display = 'none';
            document.getElementById('editServiceHostingAmount').style.display = '';
        });

        function closeServiceModal() {
            serviceModal.style.display = 'none';
            serviceModal.setAttribute('aria-hidden', 'true');
            setServiceRowBlur(null);
        }

        serviceOpenButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                openServiceModal({
                    id: this.dataset.id,
                    type: this.dataset.type,
                    name: this.dataset.name,
                    renewalDate: this.dataset.renewalDate,
                    amount: this.dataset.amount,
                    ownershipType: this.dataset.ownershipType,
                    comment: this.dataset.comment,
                    clientName: this.dataset.clientName,
                    clientCompany: this.dataset.clientCompany
                }, row);
            });
        });

        function openClientDetailsModal(clientId) {
            const client = clientGroups[clientId];
            if (!client) {
                return;
            }

            clientDetailsName.textContent = client.client_name;
            clientDetailsCompany.textContent = client.client_company || '';
            clientDetailsEmail.textContent = client.client_email ? 'Email: ' + client.client_email : '';
            clientDetailsPhone.textContent = client.client_phone ? 'Phone: ' + client.client_phone : '';
            clientDetailsServicesBody.innerHTML = client.services.map((service) => `
                <tr>
                    <td>${service.service_name}</td>
                    <td>${service.service_type}</td>
                    <td>${service.renewal_date || 'Not set'}</td>
                    <td>${service.ownership_type === 'our' ? 'Our Side' : 'Client'}</td>
                    <td>$${Number(service.amount).toFixed(2)}</td>
                </tr>
            `).join('');

            clientDetailsModal.style.display = 'flex';
            clientDetailsModal.setAttribute('aria-hidden', 'false');
            closeClientDetailsBtn.focus();
        }

        clientGroupButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                openClientDetailsModal(this.dataset.clientId);
            });
        });

        closeClientDetailsBtn.addEventListener('click', function () {
            clientDetailsModal.style.display = 'none';
            clientDetailsModal.setAttribute('aria-hidden', 'true');
        });

        clientDetailsModal.addEventListener('click', function (e) {
            if (e.target === clientDetailsModal) {
                clientDetailsModal.style.display = 'none';
                clientDetailsModal.setAttribute('aria-hidden', 'true');
            }
        });

        closeServiceBtn.addEventListener('click', closeServiceModal);
        serviceModal.addEventListener('click', function (e) {
            if (e.target === serviceModal) {
                closeServiceModal();
            }
        });

        // Keyboard support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && serviceModal.style.display === 'flex') {
                closeServiceModal();
            }
            if (e.key === 'Escape' && clientDetailsModal.style.display === 'flex') {
                clientDetailsModal.style.display = 'none';
                clientDetailsModal.setAttribute('aria-hidden', 'true');
            }
        });

        // Renewal Details Modal
        const renewalDetailsModal = document.getElementById('renewalDetailsModal');
        const closeRenewalDetailsBtn = document.getElementById('closeRenewalDetailsModal');
        const renewalDetailsButtons = document.querySelectorAll('.open-renewal-details-modal');

        function openRenewalDetailsModal(data) {
            document.getElementById('renewalClientName').textContent = data.clientName || '';
            document.getElementById('renewalClientCompany').textContent = data.clientCompany || '';
            document.getElementById('renewalServiceName').textContent = data.serviceName || '';
            document.getElementById('renewalServiceType').innerHTML = `<span class="badge ${data.serviceType === 'domain' ? 'badge-info' : 'badge-success'}">${data.serviceType === 'domain' ? 'Domain' : 'Hosting'}</span>`;
            document.getElementById('renewalOwnershipType').textContent = data.ownershipType === 'our' ? 'Our Side' : 'Client';
            document.getElementById('renewalDate').textContent = data.renewalDate ? new Date(data.renewalDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Not set';
            document.getElementById('renewalAmount').textContent = '$' + parseFloat(data.amount || 0).toFixed(2);
            
            // Calculate days until renewal
            if (data.renewalDate) {
                const today = new Date();
                const renewal = new Date(data.renewalDate);
                const diffTime = renewal - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                let daysUntilHtml = '<span class="inline-muted">' + diffDays + ' days</span>';
                if (diffDays <= 7) {
                    daysUntilHtml = '<span class="inline-danger"><strong>' + diffDays + ' days</strong></span>';
                } else if (diffDays <= 30) {
                    daysUntilHtml = '<span class="inline-warning">' + diffDays + ' days</span>';
                }
                document.getElementById('renewalDaysUntil').innerHTML = daysUntilHtml;
            }
            
            renewalDetailsModal.style.display = 'flex';
            renewalDetailsModal.setAttribute('aria-hidden', 'false');
            closeRenewalDetailsBtn.focus();
        }

        function closeRenewalDetailsModal() {
            renewalDetailsModal.style.display = 'none';
            renewalDetailsModal.setAttribute('aria-hidden', 'true');
        }

        renewalDetailsButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                openRenewalDetailsModal({
                    clientId: this.dataset.clientId,
                    clientName: this.dataset.clientName,
                    clientCompany: this.dataset.clientCompany,
                    serviceName: this.dataset.serviceName,
                    serviceType: this.dataset.serviceType,
                    renewalDate: this.dataset.renewalDate,
                    amount: this.dataset.amount,
                    ownershipType: this.dataset.ownershipType
                });
            });
        });

        closeRenewalDetailsBtn.addEventListener('click', closeRenewalDetailsModal);
        renewalDetailsModal.addEventListener('click', function (e) {
            if (e.target === renewalDetailsModal) {
                closeRenewalDetailsModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && renewalDetailsModal.style.display === 'flex') {
                closeRenewalDetailsModal();
            }
        });

        function openAddServiceModal(data) {
            document.getElementById('addServiceClientId').value = data.clientId || '';
            document.getElementById('addServiceClientName').textContent = data.clientName || '';
            serviceEntryIndex = 0;
            serviceEntryContainer.innerHTML = '';
            appendServiceEntry();
            addServiceModal.style.display = 'flex';
            // Focus management for accessibility
            addServiceModal.setAttribute('aria-hidden', 'false');
        }

        function closeAddServiceModal() {
            addServiceModal.style.display = 'none';
            addServiceModal.setAttribute('aria-hidden', 'true');
        }

        function buildServiceEntry(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'service-block';
            wrapper.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <strong>Service ${index + 1}</strong>
                    <button type="button" class="icon-action-btn remove-service-entry" title="Remove Service"><i class="fas fa-trash-alt"></i></button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Service Type</label>
                        <div style="display:flex; gap:14px; align-items:center; padding:10px 4px;">
                            <label style="margin:0; display:flex; gap:6px; align-items:center; font-weight:500;">
                                <input type="checkbox" name="services[${index}][types][]" value="domain" checked> Domain
                            </label>
                            <label style="margin:0; display:flex; gap:6px; align-items:center; font-weight:500;">
                                <input type="checkbox" name="services[${index}][types][]" value="hosting"> Hosting
                            </label>
                        </div>
                    </div>
                    <div class="form-group"><label>Name / Domain</label><input type="text" name="services[${index}][name]" required></div>
                    <div class="form-group"><label>Renewal Date</label><input type="date" name="services[${index}][renewal_date]"></div>
                    <div class="form-group"><label>Amount</label><input type="number" step="0.01" name="services[${index}][amount]"></div>
                    <div class="form-group">
                        <label>Ownership</label>
                        <select name="services[${index}][ownership_type]">
                            <option value="client">Client</option>
                            <option value="our">Our Side</option>
                        </select>
                    </div>
                </div>
            `;

            const removeBtn = wrapper.querySelector('.remove-service-entry');
            removeBtn.addEventListener('click', function () {
                if (serviceEntryContainer.children.length > 1) {
                    wrapper.remove();
                }
            });
            return wrapper;
        }

        function appendServiceEntry() {
            const entry = buildServiceEntry(serviceEntryIndex);
            serviceEntryContainer.appendChild(entry);
            serviceEntryIndex += 1;
        }

        addServiceButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                openAddServiceModal({
                    clientId: this.dataset.clientId,
                    clientName: this.dataset.clientName
                });
            });
        });

        closeAddServiceBtn.addEventListener('click', closeAddServiceModal);
        addOtherServiceBtn.addEventListener('click', appendServiceEntry);
        addServiceModal.addEventListener('click', function (e) {
            if (e.target === addServiceModal) {
                closeAddServiceModal();
            }
        });

        // Keyboard support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && addServiceModal.style.display === 'flex') {
                closeAddServiceModal();
            }
        });

        // Handle external resource loading failures
        window.addEventListener('load', function() {
            // Check if Font Awesome loaded
            if (typeof window.FontAwesome === 'undefined') {
                console.warn('Font Awesome failed to load from CDN');
            }

            // Check if Inter font loaded
            if (document.fonts) {
                document.fonts.load('12px Inter').then(function() {
                    console.log('Inter font loaded successfully');
                }).catch(function() {
                    console.warn('Inter font failed to load, using fallback');
                    document.body.classList.add('font-fallback');
                });
            }

            // Add fallback styles for accessibility
            const style = document.createElement('style');
            style.textContent = `
                /* High contrast mode support */
                @media (prefers-contrast: high) {
                    .btn-primary { background: #000 !important; color: #fff !important; }
                    .btn-secondary { background: #fff !important; color: #000 !important; border: 2px solid #000 !important; }
                }

                /* Reduced motion support */
                @media (prefers-reduced-motion: reduce) {
                    *, *::before, *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                    }
                }

                /* Font loading fallback */
                .font-fallback {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
                }
            `;
            document.head.appendChild(style);
        });
    })();
});
</script>

<noscript>
    <style>
        .modal { display: none !important; }
        .data-table { display: block !important; }
    </style>
    <div style="background: #ffe066; color: #000; padding: 1rem; margin: 1rem 0; border-radius: 4px;" role="alert">
        <strong>JavaScript is disabled.</strong> Some interactive features like modals may not work properly. Please enable JavaScript for full functionality.
    </div>
</noscript>

<?php renderLayoutEnd(); ?>

