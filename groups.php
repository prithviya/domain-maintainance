<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_client') {
        $clientId = (int) ($_POST['client_id'] ?? 0);
        if ($clientId > 0) {
            // Delete related records to satisfy foreign key constraints
            $pdo->prepare('DELETE FROM billings WHERE client_id = ?')->execute([$clientId]);
            $pdo->prepare('DELETE FROM services WHERE client_id = ?')->execute([$clientId]);
            // Delete client
            $pdo->prepare('DELETE FROM clients WHERE id = ?')->execute([$clientId]);
        }
        header('Location: groups.php');
        exit;
    }
    if ($action === 'quick_renew_service') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        if ($serviceId > 0) {
            $pdo->prepare("UPDATE services SET renewal_date = DATE_ADD(renewal_date, INTERVAL 1 YEAR) WHERE id = ?")->execute([$serviceId]);
            header('Location: groups.php?toast=Service renewed successfully');
        } else {
            header('Location: groups.php?toast=Invalid service ID&toast_type=error');
        }
        exit;
    }
}

$groupStmt = $pdo->query(
    "SELECT c.id, c.name, c.company, c.email, c.phone, c.status, COUNT(s.id) AS service_count
     FROM clients c
     LEFT JOIN services s ON s.client_id = c.id AND s.deleted_at IS NULL
     WHERE c.deleted_at IS NULL
     GROUP BY c.id
     ORDER BY c.name ASC"
);
$groups = $groupStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all services for clients (include inactive services for modal details)
$servicesStmt = $pdo->query(
    "SELECT s.id, s.client_id, s.service_type, s.name, s.renewal_date, s.amount, s.ownership_type, s.comment, s.deleted_at
     FROM services s
     ORDER BY s.client_id, s.service_type ASC"
);
$allServices = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

// Group services by client
$clientGroups = [];
foreach ($allServices as $service) {
    $clientId = (int) $service['client_id'];
    if (!isset($clientGroups[$clientId])) {
        $clientGroups[$clientId] = [];
    }
    $clientGroups[$clientId][] = [
        'service_id' => (int) $service['id'],
        'service_type' => $service['service_type'],
        'service_name' => $service['name'],
        'renewal_date' => $service['renewal_date'],
        'amount' => (float) $service['amount'],
        'ownership_type' => $service['ownership_type'],
        'comment' => $service['comment'] ?? '',
        'deleted_at' => $service['deleted_at'],
    ];
}

renderLayoutStart('Client Groups', 'groups', 'Welcome to Kho Groups');
?>
<style>
    .group-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }

    .group-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .group-card h3 {
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .group-card p {
        margin: 6px 0;
        color: #475569;
        font-size: 0.95rem;
    }

    .group-card .badge {
        margin-top: 10px;
    }
</style>

<div class="card" style="margin-bottom: 20px;">
    <div class="row-head">
        <h3><i class="fas fa-layer-group"></i> Welcome to Kho Groups</h3>
    </div>
    <p style="color:#475569; line-height:1.7;">This page organizes clients into grouped summaries. Use it to review
        client contacts, service counts, and group status information at a glance.</p>
</div>

<div class="card">
    <div class="row-head">
        <h3><i class="fas fa-users"></i> Client Groups</h3>
    </div>
    <?php if (!$groups): ?>
        <div class="inline-muted">No client groups available yet.</div>
    <?php else: ?>
        <div class="group-card-grid">
            <?php foreach ($groups as $group): ?>
                <button type="button" class="group-card open-group-details-modal" data-group-id="<?= (int) $group['id'] ?>"
                    style="border: none; background: none; cursor: pointer; text-align: left; padding: 0;">
                    <div
                        style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 18px; box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08); height: 100%;">
                        <h3 style="margin-bottom: 8px; font-size: 1.1rem;">
                            <?= esc(($group['company'] ?? '') ?: ($group['name'] ?? '')) ?>
                        </h3>
                        <?php if (!empty($group['company'])): ?>
                            <p style="margin: 6px 0; color: #475569; font-size: 0.95rem;"><strong>Name:</strong>
                                <?= esc($group['name'] ?? '') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($group['email'])): ?>
                            <p style="margin: 6px 0; color: #475569; font-size: 0.95rem;"><strong>Email:</strong>
                                <?= esc($group['email']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($group['phone'])): ?>
                            <p style="margin: 6px 0; color: #475569; font-size: 0.95rem;"><strong>Phone:</strong>
                                <?= esc($group['phone']) ?></p>
                        <?php endif; ?>
                        <p style="margin: 6px 0; color: #475569; font-size: 0.95rem;"><strong>Active Services:</strong>
                            <?= (int) $group['service_count'] ?></p>
                        <span class="badge <?= $group['status'] === 'Active' ? 'badge-active' : 'badge-expiring' ?>"
                            style="margin-top: 10px;"><?= esc($group['status']) ?></span>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="groupDetailsModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center; padding:16px;"
    role="dialog" aria-modal="true" aria-labelledby="groupDetailsModalTitle">
    <div class="card" style="width:100%; max-width:760px; max-height:90vh; overflow:auto;">
        <div class="row-head">
            <h3 id="groupDetailsModalTitle"><i class="fas fa-info-circle"></i> Client Details</h3>
            <div style="display: flex; gap: 8px; align-items: center;">

                <form method="post" style="margin: 0;">
                    <input type="hidden" name="action" value="delete_client">
                    <input type="hidden" name="client_id" id="deleteClientId" value="">
                    <button type="submit"
                        onclick="return confirm('Are you sure you want to permanently delete this client and all their services? This action cannot be undone.');"
                        style="background: #dc2626; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">Delete
                        Client</button>
                </form>
                <button type="button" class="btn-secondary" id="closeGroupDetailsModal"
                    aria-label="Close group details modal" style="padding: 6px 12px;">Close</button>
            </div>
        </div>
        <div style="padding: 0 0 16px;">
            <div><strong id="groupDetailsName"></strong></div>
            <div id="groupDetailsCompany" class="inline-muted"></div>
            <div id="groupDetailsEmail" class="inline-muted"></div>
            <div id="groupDetailsPhone" class="inline-muted"></div>
        </div>
        <div>
            <table class="client-details-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="">
                            Service</th>
                        <th style="">
                            Type</th>
                        <th style="">
                            Renewal</th>
                        <th style="">
                            Price</th>
                        <th style="">
                            Ownership</th>
                        <th style="">
                            Status</th>
                        <!-- <th
                            style="">
                            Action</th> -->
                    </tr>
                </thead>
                <tbody id="groupDetailsServicesBody"></tbody>

            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const groupDetailsModal = document.getElementById('groupDetailsModal');
        const closeGroupDetailsBtn = document.getElementById('closeGroupDetailsModal');
        const groupDetailsName = document.getElementById('groupDetailsName');
        const groupDetailsCompany = document.getElementById('groupDetailsCompany');
        const groupDetailsEmail = document.getElementById('groupDetailsEmail');
        const groupDetailsPhone = document.getElementById('groupDetailsPhone');
        const groupDetailsServicesBody = document.getElementById('groupDetailsServicesBody');
        const groupButtons = document.querySelectorAll('.open-group-details-modal');
        const clientGroups = <?= json_encode($clientGroups, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const groups = <?= json_encode(array_column($groups, null, 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        function openGroupDetailsModal(groupId) {
            const group = groups[groupId];
            if (!group) return;

            document.getElementById('deleteClientId').value = groupId;
            groupDetailsName.textContent = group.name;
            groupDetailsCompany.textContent = group.company || '';
            groupDetailsEmail.textContent = group.email ? 'Email: ' + group.email : '';
            groupDetailsPhone.textContent = group.phone ? 'Phone: ' + group.phone : '';

            const services = clientGroups[groupId] || [];
            groupDetailsServicesBody.innerHTML = services.map((service) => `
                <tr>
                    <td style="padding: 10px 12px; border: 1px solid #e2e8f0;">${service.service_name}</td>
                    <td style="padding: 10px 12px; border: 1px solid #e2e8f0;"><span class="badge ${service.service_type === 'domain' ? 'badge-info' : 'badge-success'}">${service.service_type === 'domain' ? 'Domain' : 'Hosting'}</span></td>
                    <td style="padding: 10px 12px; border: 1px solid #e2e8f0;">${service.renewal_date || 'Not set'}</td>
                    <td style="padding: 10px 12px; border: 1px solid #e2e8f0;">${Number(service.amount).toFixed(2)}</td>
                    <td style="padding: 10px 12px; border: 1px solid #e2e8f0;">${service.ownership_type === 'our' ? 'Our Side' : 'Client'}</td>
                    <td style="padding: 10px 12px; border: 1px solid #e2e8f0;"><span class="badge ${service.deleted_at ? 'badge-expiring' : 'badge-active'}">${service.deleted_at ? 'Inactive' : 'Active'}</span></td>
                    
                </tr>
            `).join('');

            groupDetailsModal.style.display = 'flex';
            groupDetailsModal.setAttribute('aria-hidden', 'false');
            closeGroupDetailsBtn.focus();
        }

        groupButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                openGroupDetailsModal(parseInt(this.dataset.groupId));
            });
        });

        closeGroupDetailsBtn.addEventListener('click', function () {
            groupDetailsModal.style.display = 'none';
            groupDetailsModal.setAttribute('aria-hidden', 'true');
        });

        groupDetailsModal.addEventListener('click', function (e) {
            if (e.target === groupDetailsModal) {
                groupDetailsModal.style.display = 'none';
                groupDetailsModal.setAttribute('aria-hidden', 'true');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && groupDetailsModal.style.display === 'flex') {
                groupDetailsModal.style.display = 'none';
                groupDetailsModal.setAttribute('aria-hidden', 'true');
            }
        });
    });
</script>

<?php renderLayoutEnd(); ?>