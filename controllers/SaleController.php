<?php
/**
 * Sale Controller
 */

require_once 'Controller.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Client.php';


class SaleController extends Controller {
    private $saleModel;
    private $productModel;
    private $clientModel;

    public function __construct() {
        $this->saleModel = new Sale();
        $this->productModel = new Product();
        $this->clientModel = new Client();
    }

    public function index() {
        $this->requireLogin();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $salesData = $this->saleModel->getSalesWithDetails();

        $sales = [
            'data' => array_slice($salesData, ($page - 1) * RECORDS_PER_PAGE, RECORDS_PER_PAGE),
            'current_page' => $page,
            'total_pages' => ceil(count($salesData) / RECORDS_PER_PAGE)
        ];

        $data = [
            'sales' => $sales,
            'title' => 'Gestión de Ventas',
            'base_url' => BASE_URL
        ];

        $this->view('sales/index', $data);
    }

    public function create() {
        $this->requireLogin();

        $products = $this->productModel->getWithCategory();
        $clients = $this->clientModel->getActive();

        $data = [
            'products' => $products,
            'clients' => $clients,
            'title' => 'Nueva Venta',
            'csrf_token' => $this->generateCsrf(),
            'base_url' => BASE_URL
        ];

        $this->view('sales/create', $data);
    }

    public function store() {
        $this->requireLogin();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            //$this->validateCsrf();

            $currentUser = getCurrentUser();
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                throw new Exception('Datos de venta no válidos');
            }

            $saleData = [
                'client_id' => $input['client_id'] ? (int)$input['client_id'] : null,
                'subtotal' => (float)$input['subtotal'],
                'discount' => (float)($input['discount'] ?? 0),
                'tax' => (float)($input['tax'] ?? 0),
                'total' => (float)$input['total'],
                'payment_method' => sanitizeInput($input['payment_method']),
                'status' => 'completed'
            ];

            $saleDetails = $input['items'] ?? [];

            if (empty($saleDetails)) {
                throw new Exception('No se han agregado productos a la venta');
            }

            // Validar stock
            foreach ($saleDetails as $item) {
                $product = $this->productModel->find($item['product_id']);
                if (!$product) {
                    throw new Exception("Producto no encontrado: {$item['product_name']}");
                }

                if ($product['stock'] < $item['quantity']) {
                    throw new Exception("Stock insuficiente para: {$product['name']}. Stock disponible: {$product['stock']}");
                }
            }

            $saleId = $this->saleModel->createSaleWithDetails($saleData, $saleDetails, $currentUser['id']);

            jsonResponse([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'sale_id' => $saleId,
                'sale_url' => BASE_URL . 'sales/show?id=' . $saleId
            ]);


        } catch (Exception $e) {
            jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show() {
        $this->requireLogin();

        $saleId = (int)($_GET['id'] ?? 0);
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            redirect(BASE_URL . 'sales/');
        }

        $saleDetails = $this->saleModel->getSaleDetails($saleId);
        $client = $sale['client_id'] ? $this->clientModel->find($sale['client_id']) : null;

        $data = [
            'sale' => $sale,
            'saleDetails' => $saleDetails,
            'client' => $client,
            'title' => 'Detalle de Venta',
            'base_url' => BASE_URL
        ];

        $this->view('sales/show', $data);
    }

    public function invoice() {
        $this->requireLogin();

        $saleId = (int)($_GET['id'] ?? 0);
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            redirect(BASE_URL . 'sales/');
        }

        $saleDetails = $this->saleModel->getSaleDetails($saleId);
        $client = $sale['client_id'] ? $this->clientModel->find($sale['client_id']) : null;

        $data = [
            'sale' => $sale,
            'saleDetails' => $saleDetails,
            'client' => $client,
            'title' => 'Factura',
            'base_url' => BASE_URL
        ];

        $this->view('sales/invoice', $data);
    }

    public function generatePDF() {
        $this->requireLogin();

        $saleId = (int)($_GET['id'] ?? 0);
        $sale = $this->saleModel->find($saleId);

        if (!$sale) {
            throw new Exception('Venta no encontrada');
        }

        $saleDetails = $this->saleModel->getSaleDetails($saleId);
        $client = $sale['client_id'] ? $this->clientModel->find($sale['client_id']) : null;

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="factura_' . $sale['code'] . '.pdf"');

        $data = [
            'sale' => $sale,
            'saleDetails' => $saleDetails,
            'client' => $client,
            'isPDF' => true,
            'base_url' => BASE_URL
        ];

        $this->view('sales/pdf', $data);
    }
}
