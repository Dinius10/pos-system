<?php
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . 'auth/login.php');
}

require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Client.php';

$productModel = new Product();
$clientModel = new Client();

$products = $productModel->getWithCategory();
$clients = $clientModel->getActive();

$title = 'Nueva Venta';
include __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="bi bi-plus-circle text-primary"></i>
        Nueva Venta
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= BASE_URL ?>sales/" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver a Ventas
            </a>
        </div>
    </div>
</div>

<form id="saleForm">
    <div class="row">
        <!-- Sale Form -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cart"></i>
                        Productos
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Product Search -->
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="productSearch" placeholder="Buscar producto por código o nombre...">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#productsModal">
                                <i class="bi bi-search"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                    
                    <!-- Cart Items -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="cartTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th width="120">Precio</th>
                                    <th width="100">Cantidad</th>
                                    <th width="120">Subtotal</th>
                                    <th width="50">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <tr id="emptyCart">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-cart-x display-6 mb-3"></i><br>
                                        No hay productos en el carrito
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sale Summary -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-person"></i>
                        Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Cliente</label>
                        <select class="form-select" id="clientSelect" name="client_id">
                            <option value="">Cliente General</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" data-name="<?= htmlspecialchars($client['name']) ?>">
                                    <?= htmlspecialchars($client['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calculator"></i>
                        Resumen
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotalAmount">Bs. 0.00</span>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descuento (%)</label>
                        <input type="number" class="form-control" id="discountPercent" min="0" max="100" step="0.01" value="0">
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento:</span>
                        <span id="discountAmount">Bs. 0.00</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>IVA (13%):</span>
                        <span id="taxAmount">Bs. 0.00</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary h5" id="totalAmount">Bs. 0.00</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="qr">QR</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="completeSale" disabled>
                        <i class="bi bi-check-circle"></i>
                        Completar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Products Modal -->
<div class="modal fade" id="productsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam"></i>
                    Seleccionar Producto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="modalProductSearch" placeholder="Buscar productos...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="modalProductList">
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['code']) ?></td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= formatCurrency($product['price']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $product['stock'] <= $product['min_stock'] ? 'danger' : 'success' ?>">
                                            <?= $product['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary add-to-cart" 
                                                data-id="<?= $product['id'] ?>"
                                                data-code="<?= htmlspecialchars($product['code']) ?>"
                                                data-name="<?= htmlspecialchars($product['name']) ?>"
                                                data-price="<?= $product['price'] ?>"
                                                data-stock="<?= $product['stock'] ?>"
                                                <?= $product['stock'] <= 0 ? 'disabled' : '' ?> >
                                            <i class="bi bi-plus"></i>
                                            Agregar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
$(document).ready(function() {
    let cart = [];
    let cartTotal = 0;
    const TAX_RATE = <?= TAX_RATE ?>;

    function formatCurrency(amount) {
        return 'Bs. ' + parseFloat(amount).toFixed(2);
    }

    function showToast(message, type = 'success') {
        const toastHtml = `
            <div class="toast align-items-center text-bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
    }

    function showLoading(element) {
        const $el = $(element);
        $el.prop('disabled', true);
        const originalText = $el.text();
        $el.data('original-text', originalText);
        $el.html('<span class="loading"></span> Cargando...');
    }

    function hideLoading(element) {
        const $el = $(element);
        $el.prop('disabled', false);
        $el.text($el.data('original-text'));
    }

    function calculateTotals() {
        const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const discountPercent = parseFloat($('#discountPercent').val()) || 0;
        const discountAmount = subtotal * (discountPercent / 100);
        const taxable = subtotal - discountAmount;
        const tax = taxable * TAX_RATE;
        const total = taxable + tax;

        $('#subtotalAmount').text(formatCurrency(subtotal));
        $('#discountAmount').text(formatCurrency(discountAmount));
        $('#taxAmount').text(formatCurrency(tax));
        $('#totalAmount').text(formatCurrency(total));

        cartTotal = total;
    }

    function updateCartDisplay() {
        const tbody = $('#cartItems');
        tbody.empty();
        if (cart.length === 0) {
            tbody.html(`<tr id="emptyCart">
                <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-cart-x display-6 mb-3"></i><br>No hay productos en el carrito
                </td>
            </tr>`);
            $('#completeSale').prop('disabled', true);
        } else {
            cart.forEach((item, index) => {
                tbody.append(`<tr>
                    <td><strong>${item.name}</strong><br><small class="text-muted">${item.code}</small></td>
                    <td>${formatCurrency(item.price)}</td>
                    <td><input type="number" class="form-control form-control-sm quantity-input" value="${item.quantity}" min="1" max="${item.stock}" data-index="${index}" style="width: 80px;"></td>
                    <td class="fw-bold">${formatCurrency(item.price * item.quantity)}</td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}"><i class="bi bi-trash"></i></button></td>
                </tr>`);
            });
            $('#completeSale').prop('disabled', false);
        }
        calculateTotals();
    }

    function addToCart(product) {
        const existing = cart.findIndex(i => i.id === product.id);
        if (existing !== -1) {
            if (cart[existing].quantity < product.stock) cart[existing].quantity++;
            else showToast('Stock insuficiente', 'warning');
        } else {
            cart.push({...product});
        }
        updateCartDisplay();
    }

    $(document).on('click', '.add-to-cart', function() {
        const product = {
            id: $(this).data('id'),
            code: $(this).data('code'),
            name: $(this).data('name'),
            price: parseFloat($(this).data('price')),
            stock: parseInt($(this).data('stock')),
            quantity: 1
        };
        addToCart(product);
        $('#productsModal').modal('hide');
    });

    $('#modalProductSearch').on('input', function() {
        const search = $(this).val().toLowerCase();
        $('#modalProductList tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(search));
        });
    });

    $('#productSearch').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const search = $(this).val().trim();
            const products = <?= json_encode($products) ?>;
            const product = products.find(p => p.code.toLowerCase() === search.toLowerCase());
            if (product) addToCart({
                id: product.id,
                code: product.code,
                name: product.name,
                price: parseFloat(product.price),
                stock: parseInt(product.stock),
                quantity: 1
            });
            else showToast('Producto no encontrado', 'warning');
            $(this).val('');
        }
    });

    $('#discountPercent').on('input', calculateTotals);

    $(document).on('input', '.quantity-input', function() {
        const index = $(this).data('index');
        let val = parseInt($(this).val());
        if (val < 1) val = 1;
        if (val > cart[index].stock) {
            val = cart[index].stock;
            showToast('Cantidad no puede exceder el stock', 'warning');
        }
        cart[index].quantity = val;
        updateCartDisplay();
    });

    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCartDisplay();
    });

    $('#saleForm').on('submit', function(e) {
        e.preventDefault();
        if (cart.length === 0) { showToast('Debe agregar al menos un producto', 'danger'); return; }

        const saleData = {
            client_id: $('#clientSelect').val() || null,
            items: cart,
            subtotal: cart.reduce((sum, i) => sum + i.price * i.quantity, 0),
            discount: (parseFloat($('#discountPercent').val()) || 0),
            tax: TAX_RATE,
            total: cartTotal,
            payment_method: $('#paymentMethod').val()
        };

        const submitBtn = $('#completeSale');
        showLoading(submitBtn);

        console.log(saleData);

        $.ajax({
            url: '<?= BASE_URL ?>api/sales',
            method: 'POST',
            data: JSON.stringify(saleData),
            contentType: 'application/json', 
            dataType: 'json'
        })

        .done(function(response) {
            if (response.success) {
                showToast('Venta registrada exitosamente', 'success');
                setTimeout(() => window.location.href = '<?= BASE_URL ?>sales/show?id=' + response.sale_id, 1500);
            } else showToast(response.message || 'Error al registrar la venta', 'danger');
        })
        .fail(function(xhr) {
            const resp = xhr.responseJSON || {};
            showToast(resp.message || 'Error de conexión', 'danger');
        })
        .always(function() {
            hideLoading(submitBtn);
        });
    });
});
</script>
