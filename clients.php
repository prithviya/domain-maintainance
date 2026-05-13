<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$pdo = db();

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
                    if (!in_array($type, ['domain', 'hosting']))
                        continue;

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
        $clientId = (int) ($_POST['client_id'] ?? 0);
        $clientName = trim((string) ($_POST['client_name'] ?? ''));
        $clientCompany = trim((string) ($_POST['client_company'] ?? ''));

        $serviceType = ($_POST['service_type'] ?? 'domain') === 'hosting' ? 'hosting' : 'domain';
        $name = trim((string) ($_POST['name'] ?? ''));
        $renewalDate = trim((string) ($_POST['renewal_date'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $ownershipType = ($_POST['ownership_type'] ?? 'client') === 'our' ? 'our' : 'client';
        $serviceStatus = ($_POST['service_status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $comment = trim((string) ($_POST['comment'] ?? ''));

        $deletedAt = $serviceStatus === 'inactive' ? date('Y-m-d H:i:s') : null;

        if ($clientId > 0 && $clientName !== '') {
            $stmtClient = $pdo->prepare('UPDATE clients SET name = ?, company = ? WHERE id = ?');
            $stmtClient->execute([$clientName, $clientCompany, $clientId]);
        }

        if ($serviceId > 0 && $name !== '') {
            $stmt = $pdo->prepare(
                'UPDATE services
                 SET service_type=?, name=?, renewal_date=?, amount=?, ownership_type=?, comment=?, deleted_at=?
                 WHERE id=?'
            );

            $stmt->execute([
                $serviceType,
                $name,
                $renewalDate ?: null,
                $amount,
                $ownershipType,
                $comment ?: null,
                $deletedAt,
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

$serviceFilterSql = 'c.deleted_at IS NULL';
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

renderLayoutStart('Client Listing', 'clients');
?>

<style>
    tbody tr.blur-background {
        filter: blur(1.6px);
        opacity: 0.55;
    }

    tbody tr.active-row {
        filter: none;
        opacity: 1;
    }

    .client-details-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .client-details-table th,
    .client-details-table td {
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        text-align: left;
    }

    .client-details-table th {
        background: #f8fafc;
    }

    .inline-danger {
        color: #dc2626;
    }

    .inline-warning {
        color: #5cbe6c;
    }

    .icon-action.disabled,
    .icon-action-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>

<div class="card" style="margin-bottom: 20px;">
    <div class="row-head">
        <h3><i class="fas fa-user-edit"></i> Add New Client</h3>
    </div>
    <form method="post">
        <input type="hidden" name="action" value="save_client">
        <input type="hidden" name="id" value="0">
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;">
            <div class="form-group" style="flex:1; min-width:220px;"><label for="clientName">Client Name</label><input
                    type="text" id="clientName" name="name" autocomplete="organization" required value=""></div>
            <div class="form-group" style="flex:1; min-width:220px;"><label for="clientCompany">Company</label><input
                    type="text" id="clientCompany" name="company" autocomplete="organization" value=""></div>
            <div class="form-group" style="flex:1; min-width:220px;"><label for="clientEmail">Email</label><input
                    type="email" id="clientEmail" name="email" autocomplete="email" value=""></div>
        </div>
        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:220px;"><label for="clientPhone">Phone *</label><input
                    type="tel" id="clientPhone" name="phone" autocomplete="tel" required value=""></div>
            <div class="form-group" style="flex:1; min-width:180px;"><label for="clientRenewalDate">Renewal
                    Date</label><input type="date" id="clientRenewalDate" name="renewal_date" value=""></div>
            <div class="form-group" style="width:180px;">
                <label for="clientStatus">Status</label>
                <select id="clientStatus" name="status">
                    <option value="Active" selected>Active</option>
                    <option value="Disabled">Disabled</option>
                </select>
            </div>
            <div style="display:flex; gap:8px; padding-bottom:2px;">
                <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Save Client</button>
                <a class="btn-secondary" href="clients.php">Clear</a>
            </div>
        </div>
    </form>
</div>

<div class="card table-wrap">
    <div class="row-head">
        <h3><i class="fas fa-list"></i> Services List (Detailed View)</h3>
    </div>
    <form method="get" style="margin-bottom:16px;">
        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:220px;">
                <label>Client Name</label>
                <input type="text" name="filter_client_name" value="<?= esc($filterClientName) ?>"
                    placeholder="Search by name/company">
            </div>
            <div class="form-group" style="flex:1; min-width:180px;">
                <label>Service Type</label>
                <select name="filter_service_type">
                    <option value="">All Services</option>
                    <option value="domain" <?= $filterServiceType === 'domain' ? 'selected' : '' ?>>Domain</option>
                    <option value="hosting" <?= $filterServiceType === 'hosting' ? 'selected' : '' ?>>Hosting</option>
                </select>
            </div>
            <div class="form-group" style="flex:1; min-width:180px;">
                <label>Renewal Month</label>
                <input type="month" name="filter_renewal_month" value="<?= esc($filterRenewalMonth) ?>">
            </div>
            <div style="display:flex; gap:8px; padding-bottom:2px;">
                <button class="btn-primary" type="submit"><i class="fas fa-filter"></i> Apply Filter</button>
                <a class="btn-secondary" href="clients.php">Reset</a>
            </div>
        </div>
    </form>
    <?php
    // Pagination for services
    $servicesPerPage = 10;
    $servicesPage = max(1, (int) ($_GET['services_page'] ?? 1));

    // Get total rows count (one row per service, or one row per client if they have no services)
    $servicesCountStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM clients c
         LEFT JOIN services s ON s.client_id = c.id AND s.deleted_at IS NULL
         WHERE {$serviceFilterSql}"
    );
    foreach ($serviceFilterParams as $key => $value) {
        $servicesCountStmt->bindValue($key, $value);
    }
    $servicesCountStmt->execute();
    $servicesTotal = (int) $servicesCountStmt->fetchColumn();

    $servicesTotalPages = max(1, (int) ceil($servicesTotal / $servicesPerPage));
    if ($servicesPage > $servicesTotalPages) {
        $servicesPage = $servicesTotalPages;
    }
    $servicesOffset = ($servicesPage - 1) * $servicesPerPage;

    // Fetch clients and services with pagination
    $servicesStmt = $pdo->prepare(
        "SELECT
            c.id AS client_id,
            c.name AS client_name,
            c.company AS client_company,
            c.email AS client_email,
            c.phone AS client_phone,
            c.status AS client_status,
            c.renewal_date AS client_renewal_date,
            s.id AS service_id,
            s.service_type,
            s.name AS service_name,
            s.renewal_date,
            s.amount,
            s.ownership_type,
            s.comment,
            s.deleted_at
         FROM clients c
         LEFT JOIN services s ON s.client_id = c.id AND s.deleted_at IS NULL
         WHERE {$serviceFilterSql}
         ORDER BY c.name ASC, s.service_type ASC, s.name ASC
         LIMIT :limit OFFSET :offset"
    );
    foreach ($serviceFilterParams as $key => $value) {
        $servicesStmt->bindValue($key, $value);
    }
    $servicesStmt->bindValue(':limit', $servicesPerPage, PDO::PARAM_INT);
    $servicesStmt->bindValue(':offset', $servicesOffset, PDO::PARAM_INT);
    $servicesStmt->execute();
    $allServices = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

    $clientGroups = [];
    foreach ($allServices as $service) {
        $clientId = (int) $service['client_id'];
        if (!isset($clientGroups[$clientId])) {
            $clientGroups[$clientId] = [
                'client_name' => $service['client_name'],
                'client_company' => $service['client_company'],
                'client_email' => $service['client_email'],
                'client_phone' => $service['client_phone'],
                'services' => [],
            ];
        }

        $clientGroups[$clientId]['services'][] = [
            'service_id' => (int) $service['service_id'],
            'service_type' => $service['service_type'],
            'service_name' => $service['service_name'],
            'renewal_date' => $service['renewal_date'],
            'amount' => (float) $service['amount'],
            'ownership_type' => $service['ownership_type'],
            'comment' => $service['comment'] ?? '',
        ];
    }
    ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Service Name</th>
                <th>Type</th>
                <th>Ownership</th>
                <th>Renewal Date</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$allServices): ?>
                <tr>
                    <td colspan="8" class="inline-muted">No services found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($allServices as $service): ?>
                    <tr <?= $service['deleted_at'] !== null ? 'style="opacity:0.6; text-decoration:line-through;"' : '' ?>>
                        <td>
                            <strong><?= esc($service['client_name']) ?></strong>
                            <?php if ($service['client_company']): ?>
                                <br><span class="inline-muted"><?= esc($service['client_company']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($service['service_id']): ?>
                                <?= esc($service['service_name']) ?>
                            <?php else: ?>
                                <span class="inline-muted">No services added</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($service['service_id']): ?>
                                <span class="badge <?= $service['service_type'] === 'domain' ? 'badge-info' : 'badge-success' ?>">
                                    <?= $service['service_type'] === 'domain' ? 'Domain' : 'Hosting' ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($service['service_id']): ?>
                                <span
                                    class="badge <?= $service['ownership_type'] === 'our' ? 'badge-primary' : 'badge-secondary' ?>">
                                    <?= $service['ownership_type'] === 'our' ? 'Our Side' : 'Client' ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($service['service_id']): ?>
                                <?php
                                $renewalDateValue = trim((string) ($service['renewal_date'] ?? ''));
                                $hasValidRenewalDate = $renewalDateValue !== '' && $renewalDateValue !== '0000-00-00';
                                ?>
                                <?php if ($hasValidRenewalDate): ?>
                                    <strong><?= formatAppDate($renewalDateValue) ?></strong>
                                    <?php
                                    try {
                                        $renewalDate = new DateTime($renewalDateValue);
                                        $today = new DateTime();
                                        $diff = $today->diff($renewalDate);
                                        $daysUntil = (int) $diff->format('%r%a');
                                        if ($daysUntil < 0) {
                                            echo '<br><span class="inline-danger">Overdue by ' . abs($daysUntil) . ' days</span>';
                                        } elseif ($daysUntil <= 30) {
                                            echo '<br><span class="inline-warning">In ' . $daysUntil . ' days</span>';
                                        } else {
                                            echo '<br><span class="inline-muted">In ' . $daysUntil . ' days</span>';
                                        }
                                    } catch (Throwable $e) {
                                        echo '<br><span class="inline-muted">Invalid date</span>';
                                    }
                                    ?>
                                <?php else: ?>
                                    <strong class="inline-muted">Not set</strong>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($service['service_id']): ?>
                                Rs.<?= number_format((float) $service['amount'], 2) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($service['service_id']): ?>
                                <?php if ($service['deleted_at'] !== null): ?>
                                    <span class="badge badge-expiring">Inactive</span>
                                <?php else: ?>
                                    <span class="badge badge-active">Active</span>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-links">
                                <?php if ($service['service_id']): ?>
                                    <button type="button" class="icon-action-btn open-service-edit-modal" title="Edit Service"
                                        data-id="<?= (int) $service['service_id'] ?>"
                                        data-type="<?= esc($service['service_type']) ?>"
                                        data-name="<?= esc($service['service_name']) ?>"
                                        data-renewal-date="<?= esc($service['renewal_date']) ?>"
                                        data-amount="<?= esc((string) $service['amount']) ?>"
                                        data-ownership-type="<?= esc($service['ownership_type']) ?>"
                                        data-service-status="<?= $service['deleted_at'] !== null ? 'inactive' : 'active' ?>"
                                        data-comment="<?= esc($service['comment'] ?? '') ?>"
                                        data-client-id="<?= (int) $service['client_id'] ?>"
                                        data-client-name="<?= esc($service['client_name']) ?>"
                                        data-client-company="<?= esc($service['client_company']) ?>"><i
                                            class="fas fa-pen"></i></button>
                                    <a class="icon-action icon-danger" title="Delete Service"
                                        href="clients.php?delete_service=<?= (int) $service['service_id'] ?>"
                                        onclick="return confirm('Delete this service?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="icon-action-btn open-edit-modal" title="Edit Client"
                                    data-id="<?= (int) $service['client_id'] ?>" data-name="<?= esc($service['client_name']) ?>"
                                    data-company="<?= esc($service['client_company']) ?>"
                                    data-email="<?= esc($service['client_email']) ?>"
                                    data-phone="<?= esc($service['client_phone']) ?>"
                                    data-status="<?= esc($service['client_status']) ?>"
                                    data-renewal-date="<?= esc($service['client_renewal_date']) ?>"><i
                                        class="fas fa-user-edit"></i></button>

                                <button type="button" class="icon-action-btn open-add-service-modal" title="Add New Service"
                                    data-client-id="<?= (int) $service['client_id'] ?>"
                                    data-client-name="<?= esc($service['client_name']) ?>"><i class="fas fa-plus-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($servicesTotalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $servicesTotalPages; $i++): ?>
                <?php
                $servicesPageQuery = [
                    'services_page' => $i,
                    'filter_client_name' => $filterClientName,
                    'filter_service_type' => $filterServiceType,
                    'filter_renewal_month' => $filterRenewalMonth,
                ];
                $servicesPageLink = 'clients.php?' . http_build_query($servicesPageQuery);
                ?>
                <?php if ($i === $servicesPage): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= esc($servicesPageLink) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<div id="addServiceModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;"
    role="dialog" aria-modal="true" aria-labelledby="addServiceModalTitle">
    <div class="card" style="width:100%; max-width:760px; max-height:90vh; overflow:auto;">
        <div class="row-head">
            <h3 id="addServiceModalTitle"><i class="fas fa-plus-circle"></i> Add Service for <span
                    id="addServiceClientName"></span></h3>
            <button type="button" class="btn-secondary" id="closeAddServiceModal"
                aria-label="Close add service modal">Close</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="add_service">
            <input type="hidden" name="client_id" id="addServiceClientId" value="">
            <div id="serviceEntryContainer" style="display:flex; flex-direction:column; gap:12px;"></div>
            <div style="margin-top:10px;">
                <button type="button" class="btn-secondary" id="addOtherServiceBtn"><i class="fas fa-plus"></i> Add
                    Other Service</button>
            </div>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Save Service</button>
            </div>
        </form>
    </div>
</div>

<div id="editClientModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;"
    role="dialog" aria-modal="true" aria-labelledby="editClientModalTitle">
    <div class="card" style="width:100%; max-width:700px; max-height:90vh; overflow:auto;">
        <div class="row-head">
            <h3 id="editClientModalTitle"><i class="fas fa-user-edit"></i> Edit Client Details</h3>
            <button type="button" class="btn-secondary" id="closeEditModal"
                aria-label="Close edit client modal">Close</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="save_client">
            <input type="hidden" name="id" id="editClientId" value="">
            <div class="form-grid">
                <div class="form-group"><label for="editClientName">Client Name *</label><input type="text"
                        id="editClientName" name="name" autocomplete="organization" required></div>
                <div class="form-group"><label for="editClientCompany">Company</label><input type="text"
                        id="editClientCompany" name="company" autocomplete="organization"></div>
                <div class="form-group"><label for="editClientEmail">Email</label><input type="email"
                        id="editClientEmail" name="email" autocomplete="email"></div>
                <div class="form-group"><label for="editClientPhone">Phone</label><input type="tel" id="editClientPhone"
                        name="phone" autocomplete="tel"></div>
                <div class="form-group"><label for="editClientRenewalDate">Renewal Date</label><input type="date"
                        id="editClientRenewalDate" name="renewal_date"></div>
                <div class="form-group">
                    <label for="editClientStatus">Status</label>
                    <select id="editClientStatus" name="status">
                        <option value="Active">Active</option>
                        <option value="Disabled">Disabled</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Update Client</button>
            </div>
        </form>
    </div>
</div>

<div id="editServiceModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;"
    role="dialog" aria-modal="true" aria-labelledby="editServiceModalTitle">
    <div class="card" style="width:100%; max-width:760px; max-height:90vh; overflow:auto;">
        <div class="row-head">
            <h3 id="editServiceModalTitle"><i class="fas fa-cogs"></i> Edit Service</h3>
            <button type="button" class="btn-secondary" id="closeServiceEditModal"
                aria-label="Close edit service modal">Close</button>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="update_service">
            <input type="hidden" name="service_id" id="editServiceId" value="">
            <input type="hidden" name="client_id" id="editServiceClientId" value="">
            <div class="form-grid">
                <div class="form-group"><label for="editServiceClientNameInput">Client Name *</label><input type="text"
                        id="editServiceClientNameInput" name="client_name" required></div>
                <div class="form-group"><label for="editServiceClientCompanyInput">Company</label><input type="text"
                        id="editServiceClientCompanyInput" name="client_company"></div>
            </div>
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Service Type</label>
                    <div style="display:flex; gap:20px; align-items:center; padding:10px 4px;">
                        <label
                            style="margin:0; display:flex; gap:8px; align-items:center; font-weight:500; cursor:pointer;">
                            <input type="radio" id="editServiceTypeDomain" name="service_type" value="domain" required>
                            Domain
                        </label>
                        <label
                            style="margin:0; display:flex; gap:8px; align-items:center; font-weight:500; cursor:pointer;">
                            <input type="radio" id="editServiceTypeHosting" name="service_type" value="hosting"
                                required> Hosting
                        </label>
                    </div>
                </div>
                <div class="form-group"><label for="editServiceName">Name / Domain</label><input type="text"
                        id="editServiceName" name="name" autocomplete="off" required></div>
                <div class="form-group"><label for="editServiceDomainRenewalDate">Renewal Date (Domain)</label><input
                        type="date" id="editServiceDomainRenewalDate" style="display:none;"></div>
                <div class="form-group"><label for="editServiceHostingRenewalDate">Renewal Date (Hosting)</label><input
                        type="date" id="editServiceHostingRenewalDate" style="display:none;"></div>
                <input type="hidden" id="editServiceRenewalDate" name="renewal_date" value="">
                <div class="form-group"><label for="editServiceDomainAmount">Domain Amount</label><input type="number"
                        id="editServiceDomainAmount" step="0.01" autocomplete="off" style="display:none;"></div>
                <div class="form-group"><label for="editServiceHostingAmount">Hosting Amount</label><input type="number"
                        id="editServiceHostingAmount" step="0.01" autocomplete="off" style="display:none;"></div>
                <input type="hidden" id="editServiceAmount" name="amount" value="">
                <div class="form-group">
                    <label for="editServiceOwnershipType">Ownership</label>
                    <select id="editServiceOwnershipType" name="ownership_type">
                        <option value="our">Our Side</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editServiceStatus">Service Status</label>
                    <select id="editServiceStatus" name="service_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editServiceComment">Comment</label>
                    <input type="text" id="editServiceComment" name="comment" autocomplete="off">
                </div>
            </div>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Update Service</button>
                <button type="button" id="addAnotherServiceBtn" class="btn-secondary"><i class="fas fa-plus"></i> Add
                    Another Service</button>
            </div>
        </form>
    </div>
</div>

<div id="clientDetailsModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;"
    role="dialog" aria-modal="true" aria-labelledby="clientDetailsModalTitle">
    <div class="card" style="width:100%; max-width:760px; max-height:90vh; overflow:auto;">
        <div class="row-head">
            <h3 id="clientDetailsModalTitle"><i class="fas fa-info-circle"></i> Client Details</h3>
            <button type="button" class="btn-secondary" id="closeClientDetailsModal"
                aria-label="Close client details modal">Close</button>
        </div>
        <div style="padding: 0 0 16px;">
            <div><strong id="clientDetailsName"></strong></div>
            <div id="clientDetailsCompany" class="inline-muted"></div>
            <div id="clientDetailsEmail" class="inline-muted"></div>
            <div id="clientDetailsPhone" class="inline-muted"></div>
        </div>
        <div>
            <table class="client-details-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Renewal</th>
                        <th>Ownership</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="clientDetailsServicesBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="renewalDetailsModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;"
    role="dialog" aria-modal="true" aria-labelledby="renewalDetailsModalTitle">
    <div class="card" style="width:100%; max-width:700px; max-height:90vh; overflow:auto;">
        <div class="row-head">
            <h3 id="renewalDetailsModalTitle"><i class="fas fa-calendar-check"></i> Renewal Details</h3>
            <button type="button" class="btn-secondary" id="closeRenewalDetailsModal"
                aria-label="Close renewal details modal">Close</button>
        </div>
        <div style="padding: 16px; display:flex; flex-direction:column; gap:16px;">
            <div>
                <label style="color:#64748b; font-size:0.85rem; text-transform:uppercase; font-weight:600;">Client
                    Information</label>
                <div style="margin-top:6px;">
                    <strong id="renewalClientName"></strong>
                    <div id="renewalClientCompany" class="inline-muted"></div>
                </div>
            </div>
            <div style="border-top:1px solid #e2e8f0; padding-top:16px;">
                <label style="color:#64748b; font-size:0.85rem; text-transform:uppercase; font-weight:600;">Service
                    Details</label>
                <div style="margin-top:6px;">
                    <p style="margin:6px 0;"><strong>Service Name:</strong> <span id="renewalServiceName"></span></p>
                    <p style="margin:6px 0;"><strong>Type:</strong> <span id="renewalServiceType"></span></p>
                    <p style="margin:6px 0;"><strong>Ownership:</strong> <span id="renewalOwnershipType"></span></p>
                </div>
            </div>
            <div style="border-top:1px solid #e2e8f0; padding-top:16px;">
                <label style="color:#64748b; font-size:0.85rem; text-transform:uppercase; font-weight:600;">Renewal
                    Information</label>
                <div style="margin-top:6px;">
                    <p style="margin:6px 0;"><strong>Renewal Date:</strong> <span id="renewalDate"></span></p>
                    <p style="margin:6px 0;"><strong>Amount:</strong> <span id="renewalAmount"></span></p>
                    <p style="margin:6px 0;"><strong>Days Until:</strong> <span id="renewalDaysUntil"></span></p>
                </div>
            </div>
        </div>
    </div>
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
            const clientGroups = <?= json_encode($clientGroups, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            let serviceEntryIndex = 0;
            let activeServiceRow = null;
            let currentServiceClient = null;

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
            document.addEventListener('keydown', function (e) {
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
                document.getElementById('editServiceClientId').value = data.clientId || '';
                document.getElementById('editServiceClientNameInput').value = data.clientName || '';
                document.getElementById('editServiceClientCompanyInput').value = data.clientCompany || '';
                document.getElementById('editServiceName').value = data.name || '';
                document.getElementById('editServiceComment').value = data.comment || '';
                document.getElementById('editServiceOwnershipType').value = data.ownershipType || 'client';
                document.getElementById('editServiceStatus').value = data.serviceStatus || 'active';

                const domainDateField = document.getElementById('editServiceDomainRenewalDate');
                const hostingDateField = document.getElementById('editServiceHostingRenewalDate');
                const hiddenRenewalDate = document.getElementById('editServiceRenewalDate');
                const domainAmountField = document.getElementById('editServiceDomainAmount');
                const hostingAmountField = document.getElementById('editServiceHostingAmount');
                const hiddenAmountField = document.getElementById('editServiceAmount');

                const currentType = data.type || 'domain';
                if (currentType === 'domain') {
                    document.getElementById('editServiceTypeDomain').checked = true;
                    domainDateField.style.display = '';
                    hostingDateField.style.display = 'none';
                    domainAmountField.style.display = '';
                    hostingAmountField.style.display = 'none';
                    domainDateField.value = data.renewalDate || '';
                    domainAmountField.value = data.amount || '';
                    hiddenRenewalDate.value = domainDateField.value;
                    hiddenAmountField.value = domainAmountField.value;
                } else {
                    document.getElementById('editServiceTypeHosting').checked = true;
                    domainDateField.style.display = 'none';
                    hostingDateField.style.display = '';
                    domainAmountField.style.display = 'none';
                    hostingAmountField.style.display = '';
                    hostingDateField.value = data.renewalDate || '';
                    hostingAmountField.value = data.amount || '';
                    hiddenRenewalDate.value = hostingDateField.value;
                    hiddenAmountField.value = hostingAmountField.value;
                }

                setServiceRowBlur(row || null);
                currentServiceClient = { id: data.clientId, name: data.clientName, company: data.clientCompany };
                serviceModal.style.display = 'flex';
                serviceModal.setAttribute('aria-hidden', 'false');
                document.getElementById('editServiceTypeDomain').focus();
            }

            // Handle service type radio change
            document.getElementById('editServiceTypeDomain').addEventListener('change', function () {
                document.getElementById('editServiceDomainRenewalDate').style.display = '';
                document.getElementById('editServiceHostingRenewalDate').style.display = 'none';
                document.getElementById('editServiceDomainAmount').style.display = '';
                document.getElementById('editServiceHostingAmount').style.display = 'none';
                document.getElementById('editServiceRenewalDate').value = document.getElementById('editServiceDomainRenewalDate').value;
                document.getElementById('editServiceAmount').value = document.getElementById('editServiceDomainAmount').value;
            });

            document.getElementById('editServiceTypeHosting').addEventListener('change', function () {
                document.getElementById('editServiceDomainRenewalDate').style.display = 'none';
                document.getElementById('editServiceHostingRenewalDate').style.display = '';
                document.getElementById('editServiceDomainAmount').style.display = 'none';
                document.getElementById('editServiceHostingAmount').style.display = '';
                document.getElementById('editServiceRenewalDate').value = document.getElementById('editServiceHostingRenewalDate').value;
                document.getElementById('editServiceAmount').value = document.getElementById('editServiceHostingAmount').value;
            });

            document.getElementById('editServiceDomainRenewalDate').addEventListener('input', function () {
                if (document.getElementById('editServiceTypeDomain').checked) {
                    document.getElementById('editServiceRenewalDate').value = this.value;
                }
            });
            document.getElementById('editServiceHostingRenewalDate').addEventListener('input', function () {
                if (document.getElementById('editServiceTypeHosting').checked) {
                    document.getElementById('editServiceRenewalDate').value = this.value;
                }
            });
            document.getElementById('editServiceDomainAmount').addEventListener('input', function () {
                if (document.getElementById('editServiceTypeDomain').checked) {
                    document.getElementById('editServiceAmount').value = this.value;
                }
            });
            document.getElementById('editServiceHostingAmount').addEventListener('input', function () {
                if (document.getElementById('editServiceTypeHosting').checked) {
                    document.getElementById('editServiceAmount').value = this.value;
                }
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
                        serviceStatus: this.dataset.serviceStatus,
                        comment: this.dataset.comment,
                        clientName: this.dataset.clientName,
                        clientCompany: this.dataset.clientCompany
                    }, row);
                });
            });

            document.getElementById('addAnotherServiceBtn').addEventListener('click', function () {
                if (currentServiceClient) {
                    openAddServiceModal(currentServiceClient);
                }
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
            document.addEventListener('keydown', function (e) {
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

            document.addEventListener('keydown', function (e) {
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
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && addServiceModal.style.display === 'flex') {
                    closeAddServiceModal();
                }
            });

            // Handle external resource loading failures
            window.addEventListener('load', function () {
                // Check if Font Awesome loaded
                if (typeof window.FontAwesome === 'undefined') {
                    console.warn('Font Awesome failed to load from CDN');
                }

                // Check if Inter font loaded
                if (document.fonts) {
                    document.fonts.load('12px Inter').then(function () {
                        console.log('Inter font loaded successfully');
                    }).catch(function () {
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
        .modal {
            display: none !important;
        }

        .data-table {
            display: block !important;
        }
    </style>
    <div style="background: #ffe066; color: #000; padding: 1rem; margin: 1rem 0; border-radius: 4px;" role="alert">
        <strong>JavaScript is disabled.</strong> Some interactive features like modals may not work properly. Please
        enable JavaScript for full functionality.
    </div>
</noscript>

<?php renderLayoutEnd(); ?>