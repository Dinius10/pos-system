// Main JavaScript file for POS System

// Global variables
let csrfToken = '';

// Initialize application
$(document).ready(function() {
    initializeApp();
    setupEventListeners();
    setupAjaxDefaults();
});

// Initialize application
function initializeApp() {
    // Generate CSRF token
    generateCSRFToken();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Add active class to current navigation item
    highlightActiveNavigation();
}

// Setup global event listeners
function setupEventListeners() {
    // Confirm delete actions
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        if (confirm('¿Está seguro que desea eliminar este elemento?')) {
            $(this).closest('form').submit();
        }
    });
    
    // Format currency inputs
    $(document).on('input', '.currency-input', function() {
        let value = $(this).val().replace(/[^\d.]/g, '');
        $(this).val(value);
    });
    
    // Validate numeric inputs
    $(document).on('input', '.numeric-input', function() {
        let value = $(this).val().replace(/[^\d]/g, '');
        $(this).val(value);
    });
    
    // Auto-submit search forms
    $(document).on('input', '.search-input', debounce(function() {
        const form = $(this).closest('form');
        if (form.length) {
            form.submit();
        }
    }, 500));
    
    // Handle form submissions
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        submitAjaxForm($(this));
    });
}

// Setup AJAX defaults
function setupAjaxDefaults() {
    $.ajaxSetup({
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        },
        error: function(xhr, status, error) {
            if (xhr.status === 401) {
                window.location.href = '/pos-system/auth/login.php';
            } else if (xhr.status === 403) {
                showToast('No tiene permisos para realizar esta acción', 'error');
            } else if (xhr.status === 500) {
                showToast('Error interno del servidor', 'error');
            }
        }
    });
}

// Generate CSRF token
function generateCSRFToken() {
    csrfToken = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    $('<meta>').attr('name', 'csrf-token').attr('content', csrfToken).appendTo('head');
}

// Highlight active navigation
function highlightActiveNavigation() {
    const currentPath = window.location.pathname;
    $('.nav-link').each(function() {
        const href = $(this).attr('href');
        if (href && currentPath.includes(href)) {
            $(this).addClass('active');
        }
    });
}

// Submit AJAX form
function submitAjaxForm(form) {
    const url = form.attr('action') || form.data('action');
    const method = form.attr('method') || 'POST';
    const data = form.serialize();
    const submitBtn = form.find('button[type="submit"]');
    
    if (!url) {
        showToast('URL de acción no definida', 'error');
        return;
    }
    
    showLoading(submitBtn);
    
    $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json'
    })
    .done(function(response) {
        if (response.success) {
            showToast(response.message || 'Operación exitosa', 'success');
            
            if (response.redirect) {
                setTimeout(function() {
                    window.location.href = response.redirect;
                }, 1500);
            } else if (response.reload) {
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
        } else {
            showToast(response.message || 'Error en la operación', 'error');
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON || {};
        showToast(response.message || 'Error de conexión', 'error');
    })
    .always(function() {
        hideLoading(submitBtn);
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Data table initialization
function initializeDataTable(selector, options = {}) {
    const defaultOptions = {
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        }
    };
    
    const finalOptions = Object.assign({}, defaultOptions, options);
    return $(selector).DataTable(finalOptions);
}

// Chart utilities
function createChart(canvasId, type, data, options = {}) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };
    
    const finalOptions = Object.assign({}, defaultOptions, options);
    
    return new Chart(ctx, {
        type: type,
        data: data,
        options: finalOptions
    });
}

// Local storage utilities
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (e) {
        console.error('Error saving to localStorage:', e);
        return false;
    }
}

function getFromLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (e) {
        console.error('Error reading from localStorage:', e);
        return defaultValue;
    }
}

function removeFromLocalStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (e) {
        console.error('Error removing from localStorage:', e);
        return false;
    }
}

// Print utilities
function printElement(selector) {
    const element = $(selector);
    if (element.length) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Imprimir</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            .no-print { display: none !important; }
                            body { font-size: 12px; }
                        }
                    </style>
                </head>
                <body>
                    ${element.html()}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    }
}

// Export utilities
function exportToCSV(data, filename) {
    if (!data || !data.length) {
        showToast('No hay datos para exportar', 'warning');
        return;
    }
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => `"${row[header] || ''}"`).join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Date utilities
function formatDate(date, format = 'dd/mm/yyyy') {
    if (!date) return '';
    
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    
    switch (format) {
        case 'dd/mm/yyyy':
            return `${day}/${month}/${year}`;
        case 'yyyy-mm-dd':
            return `${year}-${month}-${day}`;
        case 'dd-mm-yyyy':
            return `${day}-${month}-${year}`;
        default:
            return d.toLocaleDateString();
    }
}

function formatDateTime(dateTime, format = 'dd/mm/yyyy hh:mm') {
    if (!dateTime) return '';
    
    const dt = new Date(dateTime);
    const date = formatDate(dt, 'dd/mm/yyyy');
    const time = dt.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    
    return `${date} ${time}`;
}

// Validation utilities
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{7,8}$/;
    return re.test(phone);
}

function validateCI(ci) {
    return ci && ci.length >= 7 && /^\d+$/.test(ci);
}

// Form validation
function validateForm(form) {
    let isValid = true;
    
    form.find('input[required], select[required], textarea[required]').each(function() {
        const field = $(this);
        const value = field.val().trim();
        
        if (!value) {
            showFieldError(field, 'Este campo es requerido');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Validate email fields
    form.find('input[type="email"]').each(function() {
        const field = $(this);
        const value = field.val().trim();
        
        if (value && !validateEmail(value)) {
            showFieldError(field, 'Email no válido');
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    field.addClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
    field.after(`<div class="invalid-feedback">${message}</div>`);
}

function clearFieldError(field) {
    field.removeClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
}

// Loading states
function showPageLoading() {
    if (!$('#page-loader').length) {
        $('body').append(`
            <div id="page-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                 background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);
    }
}

function hidePageLoading() {
    $('#page-loader').remove();
}

// Session management
function checkSession() {
    $.get('/pos-system/api/auth/check')
    .fail(function() {
        window.location.href = '/pos-system/auth/login.php';
    });
}

// Auto-check session every 5 minutes
setInterval(checkSession, 300000);

// Prevent session timeout by keeping it active
$(document).on('click mousemove keypress', function() {
    // Update last activity timestamp
    const now = Date.now();
    const lastActivity = getFromLocalStorage('lastActivity', 0);
    
    // Only make request if more than 1 minute has passed
    if (now - lastActivity > 60000) {
        saveToLocalStorage('lastActivity', now);
        checkSession();
    }
});

// Initialize session activity tracking
saveToLocalStorage('lastActivity', Date.now());