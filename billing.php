<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$pdo = db();

function redirectBilling(string $message = '', string $type = 'success'): void
{
    $query = [];
    if ($message !== '') {
        $query['toast'] = $message;
        $query['toast_type'] = $type;
    }
    $url = 'billing.php';
    if ($query) {
        $url .= '?' . http_build_query($query);
    }
    header('Location: ' . $url);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_billing') {
        $id = (int) ($_POST['id'] ?? 0);
        $clientId = (int) ($_POST['client_id'] ?? 0);
        $serviceTypeInput = $_POST['service_type'] ?? [];
        if (!is_array($serviceTypeInput)) {
            $serviceTypeInput = [$serviceTypeInput];
        }
        $serviceTypes = [];
        foreach ($serviceTypeInput as $type) {
            if ($type === 'domain' || $type === 'hosting') {
                $serviceTypes[] = $type;
            }
        }
        $serviceTypes = array_values(array_unique($serviceTypes));
        $serviceRef = trim((string) ($_POST['service_ref'] ?? ''));
        $renewalDate = trim((string) ($_POST['renewal_date'] ?? ''));
        $lastBillingDate = trim((string) ($_POST['last_billing_date'] ?? ''));
        $amount = (float) ($_POST['amount'] ?? 0);
        $paymentMode = ($_POST['payment_mode'] ?? 'Bank Transfer') === 'GPay' ? 'GPay' : 'Bank Transfer';
        $status = ($_POST['status'] ?? 'Active') === 'Paid' ? 'Paid' : 'Active';

        if ($clientId > 0 && $serviceRef !== '' && $renewalDate !== '') {
            if (empty($serviceTypes)) {
                $serviceTypes = ['domain']; // Default to domain if none specified
            }
            $checkClientStmt = $pdo->prepare('SELECT id FROM clients WHERE id = ? AND deleted_at IS NULL');
            $checkClientStmt->execute([$clientId]);
            $clientExists = (bool) $checkClientStmt->fetchColumn();
            if (!$clientExists) {
                redirectBilling('Selected client not found', 'error');
            }

            if ($id > 0) {
                $serviceType = $serviceTypes[0];
                $stmt = $pdo->prepare(
                    'UPDATE billings
                     SET client_id = ?, service_type = ?, service_ref = ?, renewal_date = ?, last_billing_date = ?, amount = ?, payment_mode = ?, status = ?
                     WHERE id = ? AND deleted_at IS NULL'
                );
                $stmt->execute([$clientId, $serviceType, $serviceRef, $renewalDate, $lastBillingDate ?: null, $amount, $paymentMode, $status, $id]);
                
                // If status is Paid, update the corresponding services' renewal dates
                if ($status === 'Paid') {
                    $selectedServiceIds = $_POST['selected_service_ids'] ?? [];
                    if (!empty($selectedServiceIds)) {
                        $placeholders = implode(',', array_fill(0, count($selectedServiceIds), '?'));
                        $updateParams = array_merge([$renewalDate], $selectedServiceIds);
                        $stmtS = $pdo->prepare("UPDATE services SET renewal_date = ? WHERE id IN ($placeholders)");
                        $stmtS->execute($updateParams);
                    }
                }
                redirectBilling('Billing updated successfully');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO billings (client_id, service_type, service_ref, renewal_date, last_billing_date, amount, payment_mode, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                foreach ($serviceTypes as $serviceType) {
                    $stmt->execute([$clientId, $serviceType, $serviceRef, $renewalDate, $lastBillingDate ?: null, $amount, $paymentMode, $status]);
                }
                
                // If status is Paid, update the corresponding services' renewal dates
                if ($status === 'Paid') {
                    $selectedServiceIds = $_POST['selected_service_ids'] ?? [];
                    if (!empty($selectedServiceIds)) {
                        $placeholders = implode(',', array_fill(0, count($selectedServiceIds), '?'));
                        $updateParams = array_merge([$renewalDate], $selectedServiceIds);
                        $stmtS = $pdo->prepare("UPDATE services SET renewal_date = ? WHERE id IN ($placeholders)");
                        $stmtS->execute($updateParams);
                    }
                }
                redirectBilling('Billing added successfully');
            }
        }
        redirectBilling('Client, service type, service name, and renewal date are required', 'error');
    }

    redirectBilling();
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare('UPDATE billings SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    redirectBilling('Billing deleted successfully');
}

if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE billings SET status = CASE WHEN status = 'Active' THEN 'Disabled' ELSE 'Active' END WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$id]);
    redirectBilling('Billing status updated');
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editBilling = null;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM billings WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$editId]);
    $editBilling = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$clients = $pdo->query('SELECT id, name, company FROM clients WHERE deleted_at IS NULL ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query('SELECT client_id, id, service_type, name, renewal_date, amount FROM services WHERE deleted_at IS NULL ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$billPerPage = 10;
$billPage = max(1, (int) ($_GET['bill_page'] ?? 1));
$filterClientName = trim((string) ($_GET['filter_client_name'] ?? ''));
$filterServiceType = (string) ($_GET['filter_service_type'] ?? '');
$filterBillingMonth = trim((string) ($_GET['filter_billing_month'] ?? ''));
$filterRenewalMonth = trim((string) ($_GET['filter_renewal_month'] ?? ''));

if (!in_array($filterServiceType, ['', 'domain', 'hosting'], true)) {
    $filterServiceType = '';
}
if ($filterBillingMonth !== '' && !preg_match('/^\d{4}-\d{2}$/', $filterBillingMonth)) {
    $filterBillingMonth = '';
}
if ($filterRenewalMonth !== '' && !preg_match('/^\d{4}-\d{2}$/', $filterRenewalMonth)) {
    $filterRenewalMonth = '';
}

$billWhere = [
    'b.deleted_at IS NULL',
    'c.deleted_at IS NULL',
];
$billParams = [];

if ($filterClientName !== '') {
    $billWhere[] = 'c.name LIKE :client_name';
    $billParams[':client_name'] = '%' . $filterClientName . '%';
}
if ($filterServiceType !== '') {
    $billWhere[] = 'b.service_type = :service_type';
    $billParams[':service_type'] = $filterServiceType;
}
if ($filterBillingMonth !== '') {
    $billWhere[] = 'DATE_FORMAT(b.last_billing_date, "%Y-%m") = :billing_month';
    $billParams[':billing_month'] = $filterBillingMonth;
}
if ($filterRenewalMonth !== '') {
    $billWhere[] = 'DATE_FORMAT(b.renewal_date, "%Y-%m") = :renewal_month';
    $billParams[':renewal_month'] = $filterRenewalMonth;
}

$billWhereSql = implode(' AND ', $billWhere);

$billCountStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM billings b
     JOIN clients c ON c.id = b.client_id
     WHERE {$billWhereSql}"
);
foreach ($billParams as $key => $value) {
    $billCountStmt->bindValue($key, $value);
}
$billCountStmt->execute();
$billTotal = (int) $billCountStmt->fetchColumn();

$billTotalPages = max(1, (int) ceil($billTotal / $billPerPage));
if ($billPage > $billTotalPages) {
    $billPage = $billTotalPages;
}
$billOffset = ($billPage - 1) * $billPerPage;

$billingStmt = $pdo->prepare(
    "SELECT b.*, c.name AS client_name, c.company AS client_company
     FROM billings b
     JOIN clients c ON c.id = b.client_id
     WHERE {$billWhereSql}
     ORDER BY DATE(b.renewal_date) ASC, b.id DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($billParams as $key => $value) {
    $billingStmt->bindValue($key, $value);
}
$billingStmt->bindValue(':limit', $billPerPage, PDO::PARAM_INT);
$billingStmt->bindValue(':offset', $billOffset, PDO::PARAM_INT);
$billingStmt->execute();
$billingRows = $billingStmt->fetchAll(PDO::FETCH_ASSOC);

renderLayoutStart('Billing Management', 'billing');
?>

<style>
    .billing-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    @media (max-width: 980px) {
        .billing-form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .billing-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card" style="margin-bottom: 20px;">
    <div class="row-head">
        <h3><i class="fas fa-file-invoice-dollar"></i> <?= $editBilling ? 'Edit Billing' : 'Add Renewal / Billing' ?>
        </h3>
    </div>
    <form method="post">
        <input type="hidden" name="action" value="save_billing">
        <input type="hidden" name="id" value="<?= (int) ($editBilling['id'] ?? 0) ?>">
        <div class="billing-form-grid">
            <div class="form-group">
                <label>Select Client *</label>
                <select name="client_id" required>
                    <option value="">-- Select Client --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= (int) $client['id'] ?>" <?= ((int) ($editBilling['client_id'] ?? 0) === (int) $client['id']) ? 'selected' : '' ?>>
                            <?= esc($client['company'] ? $client['company'] . ' / ' . $client['name'] : $client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Service Name & Details *</label>
                <input type="hidden" name="service_ref" id="serviceRefText" value="<?= esc($editBilling['service_ref'] ?? '') ?>">
                <input type="hidden" name="service_type[]" value="domain">
                <div id="selectedServiceIdsContainer"></div>
                <div id="serviceRefDisplay" style="font-size: 0.85rem; color: #1e293b; font-weight: 600; margin-bottom: 5px; padding: 5px; background: #eef2ff; border-radius: 8px; display: <?= !empty($editBilling['service_ref']) ? 'block' : 'none' ?>;">
                    <?= esc($editBilling['service_ref'] ?? '') ?>
                </div>
                <div id="clientServicesCheckboxes"
                    style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 8px; max-height: 250px; overflow-y: auto; padding: 10px; border: 1px dashed #cbd5e6; border-radius: 12px; background: #fff; display:none;">
                    <!-- Populated by JS -->
                </div>
            </div>
            <div class="form-group"><label>Next Renewal Date *</label><input type="date" name="renewal_date" required
                    readonly value="<?= esc($editBilling['renewal_date'] ?? date('Y-m-d', strtotime('+365 days'))) ?>">
            </div>
            <div class="form-group"><label>Bill Paid Date</label><input type="date" name="last_billing_date"
                    value="<?= esc($editBilling['last_billing_date'] ?? '') ?>"></div>
            <div class="form-group"><label>Amount</label><input type="number" step="0.01" name="amount"
                    value="<?= esc(isset($editBilling['amount']) ? (string) $editBilling['amount'] : '') ?>"></div>
            <div class="form-group">
                <label>Payment Mode</label>
                <select name="payment_mode">
                    <option value="Bank Transfer" <?= (($editBilling['payment_mode'] ?? 'Bank Transfer') === 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="GPay" <?= (($editBilling['payment_mode'] ?? '') === 'GPay') ? 'selected' : '' ?>>GPay
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Paid" <?= (($editBilling['status'] ?? '') === 'Paid') ? 'selected' : '' ?>>Paid
                    </option>
                    <option value="Active" <?= (($editBilling['status'] ?? 'Active') === 'Active') ? 'selected' : '' ?>>Not
                        Paid</option>

                </select>
            </div>
        </div>
        <div style="margin-top: 12px; display: flex; gap: 8px;">
            <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Save Billing</button>
            <a class="btn-secondary" href="billing.php">Clear</a>
        </div>
    </form>
</div>

<div class="card table-wrap">
    <div class="row-head">
        <h3><i class="fas fa-credit-card"></i> Billing List</h3>
    </div>
    <form method="get" style="margin-bottom:16px;">
        <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:220px;">
                <label>Client Name</label>
                <input type="text" name="filter_client_name" value="<?= esc($filterClientName) ?>"
                    placeholder="Search by client">
            </div>
            <div class="form-group" style="flex:1; min-width:180px;">
                <label>Service Based</label>
                <select name="filter_service_type">
                    <option value="">All Services</option>
                    <option value="domain" <?= $filterServiceType === 'domain' ? 'selected' : '' ?>>Domain</option>
                    <option value="hosting" <?= $filterServiceType === 'hosting' ? 'selected' : '' ?>>Hosting</option>
                </select>
            </div>
            <div class="form-group" style="flex:1; min-width:180px;">
                <label>Billing (Month)</label>
                <input type="month" name="filter_billing_month" value="<?= esc($filterBillingMonth) ?>">
            </div>
            <div class="form-group" style="flex:1; min-width:180px;">
                <label>Renewal (Month)</label>
                <input type="month" name="filter_renewal_month" value="<?= esc($filterRenewalMonth) ?>">
            </div>
            <div style="display:flex; gap:8px; padding-bottom:2px;">
                <button class="btn-primary" type="submit"><i class="fas fa-filter"></i> Apply Filter</button>
                <a class="btn-secondary" href="billing.php">Reset</a>
            </div>
        </div>
    </form>
    <table class="data-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Type</th>
                <th>Service</th>
                <th>Next Renewal</th>
                <th>Last Bill Paid Date </th>
                <th>Amount</th>
                <th>Payment Mode</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$billingRows): ?>
                <tr>
                    <td colspan="9" class="inline-muted">No billing records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($billingRows as $row): ?>
                    <tr>
                        <td>
                            <strong><?= esc($row['client_name']) ?></strong>
                            <?php if (!empty($row['client_company'])): ?>
                                <br><small class="inline-muted"><?= esc($row['client_company']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['service_type'] === 'domain' ? 'Domain' : 'Hosting' ?></td>
                        <td><?= esc($row['service_ref']) ?></td>
                        <td><?= formatAppDate($row['renewal_date']) ?></td>
                        <td><?= formatAppDate($row['last_billing_date']) ?></td>
                        <td><?= number_format((float) $row['amount'], 2) ?></td>
                        <td><?= esc($row['payment_mode'] ?? 'Bank Transfer') ?></td>
                        <td><span
                                class="badge <?= $row['status'] === 'Active' ? 'badge-active' : 'badge-expiring' ?>"><?= esc($row['status']) ?></span>
                        </td>
                        <td>
                            <div class="action-links">
                                <a class="icon-action" title="Edit Billing" href="billing.php?edit=<?= (int) $row['id'] ?>">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a class="icon-action icon-toggle"
                                    title="<?= $row['status'] === 'Active' ? 'Disable Billing' : 'Enable Billing' ?>"
                                    href="billing.php?toggle=<?= (int) $row['id'] ?>">
                                    <i class="fas <?= $row['status'] === 'Active' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                                </a>
                                <a class="icon-action icon-danger" title="Delete Billing"
                                    href="billing.php?delete=<?= (int) $row['id'] ?>"
                                    onclick="return confirm('Delete this billing record?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($billTotalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $billTotalPages; $i++): ?>
                <?php
                $billPageQuery = [
                    'bill_page' => $i,
                    'filter_client_name' => $filterClientName,
                    'filter_service_type' => $filterServiceType,
                    'filter_billing_month' => $filterBillingMonth,
                    'filter_renewal_month' => $filterRenewalMonth,
                ];
                foreach ($billPageQuery as $queryKey => $queryValue) {
                    if ($queryValue === '') {
                        unset($billPageQuery[$queryKey]);
                    }
                }
                $billPageLink = 'billing.php?' . http_build_query($billPageQuery);
                ?>
                <?php if ($i === $billPage): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= esc($billPageLink) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    (function () {
        const clientsServices = <?= json_encode($services) ?>;
        const clientSelect = document.querySelector('select[name="client_id"]');
        const serviceRefText = document.getElementById('serviceRefText');
        const checkboxesContainer = document.getElementById('clientServicesCheckboxes');

        function updateServiceCheckboxes() {
            const clientId = clientSelect.value;
            checkboxesContainer.innerHTML = '';

            if (!clientId) {
                checkboxesContainer.style.display = 'none';
                return;
            }

            const filteredServices = clientsServices.filter(s => s.client_id == clientId);
            if (filteredServices.length > 0) {
                checkboxesContainer.style.display = 'flex';
                filteredServices.forEach((service) => {
                    const label = document.createElement('label');
                    label.style = "display:flex; justify-content: space-between; align-items:center; font-size:0.9rem; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius:12px; cursor:pointer; background: #fafafa; margin-bottom: 6px; width: 100%; min-width: 250px;";

                    const leftPart = document.createElement('div');
                    leftPart.style = "display:flex; gap:10px; align-items:center;";

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.value = `${service.name} (${service.service_type})`;
                    checkbox.dataset.id = service.id;
                    checkbox.dataset.amount = service.amount || 0;

                    checkbox.addEventListener('change', () => {
                        updateTextarea();
                    });

                    leftPart.appendChild(checkbox);
                    leftPart.appendChild(document.createTextNode(`${service.name}`));

                    const rightPart = document.createElement('div');
                    rightPart.style = "font-size: 0.8rem; color: #64748b; text-align: right;";

                    const typeBadge = `<span class="badge ${service.service_type === 'domain' ? 'badge-info' : 'badge-success'}" style="margin-bottom:2px; display:inline-block; padding: 2px 8px;">${service.service_type}</span>`;
                    const dateInfo = service.renewal_date ? `<br>Exp: ${service.renewal_date}` : '<br>Exp: -';
                    const amountInfo = `<br><strong>Rs.${Number(service.amount || 0).toLocaleString()}</strong>`;

                    rightPart.innerHTML = typeBadge + dateInfo + amountInfo;

                    label.appendChild(leftPart);
                    label.appendChild(rightPart);
                    checkboxesContainer.appendChild(label);
                });
            } else {
                checkboxesContainer.style.display = 'none';
            }
        }

        function updateTextarea() {
            const checkedBoxes = Array.from(checkboxesContainer.querySelectorAll('input:checked'));
            const selectedText = checkedBoxes.map(cb => cb.value).join(', ');
            serviceRefText.value = selectedText;
            
            // Auto-calculate amount
            const totalAmount = checkedBoxes.reduce((sum, cb) => sum + parseFloat(cb.dataset.amount || 0), 0);
            const amountInput = document.querySelector('input[name="amount"]');
            if (amountInput) {
                amountInput.value = totalAmount.toFixed(2);
            }

            // Update hidden service IDs
            const idsContainer = document.getElementById('selectedServiceIdsContainer');
            if (idsContainer) {
                idsContainer.innerHTML = '';
                checkedBoxes.forEach(cb => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'selected_service_ids[]';
                    hidden.value = cb.dataset.id;
                    idsContainer.appendChild(hidden);
                });
            }

            const display = document.getElementById('serviceRefDisplay');
            if (display) {
                display.innerText = selectedText;
                display.style.display = selectedText ? 'block' : 'none';
            }
        }

        clientSelect.addEventListener('change', updateServiceCheckboxes);

        // Handle edit mode where client is already selected
        if (clientSelect.value) {
            // In edit mode, we might not want to clear the textarea immediately,
            // but we should show the checkboxes.
            updateServiceCheckboxes();
        }
    })();
</script>

<?php renderLayoutEnd(); ?>