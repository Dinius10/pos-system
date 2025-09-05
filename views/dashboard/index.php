<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect('/pos-system/auth/login.php');
}

require_once __DIR__ . '/../../models/Sale.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Client.php';

$saleModel = new Sale();
$productModel = new Product();
$clientModel = new Client();

// Get today's sales
$todaySales = $saleModel->getDailySales();
$lowStockProducts = $productModel->getLowStock();
$topProducts = $productModel->getTopSelling();
$topClients = $clientModel->getTopClients();

// Get monthly sales data for chart
$monthlySales = $saleModel->getMonthlySales();

$title = 'Dashboard';
include __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-speedometer2 text-primary"></i>
        Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar"></i>
                <?= date('d/m/Y') ?>
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Ventas Hoy</div>
                        <div class="h4 mb-0 text-white"><?= $todaySales['total_sales'] ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-receipt display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Ingresos Hoy</div>
                        <div class="h4 mb-0 text-white"><?= formatCurrency($todaySales['total_amount']) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Stock Bajo</div>
                        <div class="h4 mb-0 text-white"><?= count($lowStockProducts) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Productos</div>
                        <div class="h4 mb-0 text-white"><?= count($topProducts) ?>+</div>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box-seam display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sales Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up text-primary"></i>
                    Ventas del Mes
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-star text-warning"></i>
                    Productos Top
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($topProducts)): ?>
                    <p class="text-muted text-center">No hay datos disponibles</p>
                <?php else: ?>
                    <?php foreach ($topProducts as $product): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                                <small class="text-muted">Vendidos: <?= $product['total_sold'] ?></small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success"><?= formatCurrency($product['total_revenue']) ?></div>
                            </div>
                        </div>
                        <?php if (!$loop->last): ?>
                            <hr class="my-2">
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Low Stock Alert -->
    <?php if (!empty($lowStockProducts)): ?>
    <div class="col-lg-6 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    Productos con Stock Bajo
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($lowStockProducts, 0, 5) as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td>
                                        <span class="badge bg-warning"><?= $product['stock'] ?></span>
                                    </td>
                                    <td><?= $product['min_stock'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center">
                    <a href="/pos-system/products/" class="btn btn-warning btn-sm">Ver todos</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Top Clients -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people text-info"></i>
                    Mejores Clientes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($topClients)): ?>
                    <p class="text-muted text-center">No hay datos disponibles</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Compras</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topClients as $client): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($client['name']) ?></td>
                                        <td><?= $client['total_purchases'] ?></td>
                                        <td class="text-success fw-bold"><?= formatCurrency($client['total_spent']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
<script>
$(document).ready(function() {
    // Sales Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?= json_encode($monthlySales) ?>;
    
    const labels = salesData.map(item => {
        const date = new Date(item.date);
        return date.getDate() + '/' + (date.getMonth() + 1);
    });
    
    const data = salesData.map(item => parseFloat(item.daily_total || 0));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas Diarias',
                data: data,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Bs. ' + value.toFixed(2);
                        }
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            elements: {
                point: {
                    hoverBackgroundColor: '#2563eb'
                }
            }
        }
    });
});
</script>

