<?php
session_start();
require 'php/config/db.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT id, nombre, rol FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Validar rol
if (!in_array($usuario['rol'], ['admin', 'director'])) {
    header('Location: acceso-denegado.html');
    exit;
}

// Configurar variables
$nombre_completo = $usuario['nombre'];
$rol = $usuario['rol'];

$query = "SELECT 
            SUM(CASE WHEN genero = 'M' THEN 1 ELSE 0 END) as ninos,
            SUM(CASE WHEN genero = 'F' THEN 1 ELSE 0 END) as ninas
          FROM estudiantes";
$resultado = $conexion->query($query);
$data = $resultado->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Asistencia - Admin</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&display=swap" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <style>
    .disabled {
        opacity: 0.6;
        pointer-events: none;
    }
    .admin-only {
        display: <?php echo ($rol === 'admin') ? 'block' : 'none'; ?>;
    }
    .read-only-banner {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background:rgb(41, 80, 253);
        padding: 15px;
        border-radius: 8px;
        z-index: 9999;
    }
    </style>
    
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin.php">
                <div class="sidebar-brand-icon">
                <img src="img/OIP.png" alt="Logo Asistencia" style="width: 70px; height: 70px">
                </div>
                <div class="sidebar-brand-text mx-3">EduTrack<sup></sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="reportes.html">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Reportes Generales</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?php echo htmlspecialchars($nombre_completo); ?> 
                                <span class="badge badge-secondary"><?php echo strtoupper($rol); ?></span>
                                </span>
                                <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logouth.php" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Reportes de Asistencia</h1>
                            <button id="generateReport" class="btn btn-success shadow-sm">
                                <i class="fas fa-file-export mr-2"></i>Generar Reporte
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="generarReporte('pdf')">
                                    <i class="fas fa-file-pdf text-danger mr-2"></i>PDF
                                </a>
                                <a class="dropdown-item" href="#" onclick="generarReporte('excel')">
                                    <i class="fas fa-file-excel text-success mr-2"></i>Excel
                                </a>
                            </div>
                            
                    </div>
                
                <div class="salpicadero-container">   
                    <div class="salpicadero-info">
                        <div class="iframe-container">

                                <!-- Begin Page Content -->
    <div class="container-fluid">
        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tablaAsistencia">
                    <thead class="thead-light">
                        <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Cédula</th>
                        <th>Año</th>
                        <th>Sección</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                            <!-- Aquí se llenarán los datos desde la base de datos -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
    <!-- /.container-fluid -->

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>


    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const rol = "<?php echo $rol; ?>";
        
        // Modo director
        if(rol === 'director') {
            document.querySelectorAll('.admin-only').forEach(el => el.remove());
            document.querySelectorAll('button, input, select').forEach(el => {
                if(!el.classList.contains('safe-action')) {
                    el.disabled = true;
                    el.classList.add('disabled');
                }
            });
        }

        // Cargar datos
        document.addEventListener('DOMContentLoaded', () => {
            fetch('php/controllers/get_asistencia.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#tablaAsistencia tbody');
                tbody.innerHTML = data.map(registro => `
                    <tr>
                        <td>${registro.nombre}</td>
                        <td>${registro.apellido}</td>
                        <td>${registro.cedula}</td>
                        <td>${registro.año}</td>
                        <td>${registro.seccion}</td>
                        <td>${registro.entrada || '--'}</td>
                        <td>${registro.salida || '--'}</td>
                    </tr>
            `).join('');
                
                $('#dataTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                    }
                });
            });
    });
    </script>
    <!-- Script para generar reporte -->
    <script>
        // En tu archivo JS
    function generarReporte(formato) {
        window.location.href = `php/controllers/generar_reporte.php?formato=${formato}`;
}

        // Llenar la tabla con datos de la base de datos
        // Actualizar el fetch para obtener datos combinados
        fetch('php/controllers/api.php?action=getAttendanceData')
        .then(response => response.json())
        .then(data => {
             const tbody = document.querySelector('#dataTable tbody');
            tbody.innerHTML = ''; // Limpiar tabla
    
        data.forEach(registro => {
            const tr = document.createElement('tr');
            .innerHTML = `
                <td>${registro.nombre}</td>
                <td>${registro.apellido}</td>
                <td>${registro.cedula}</td>
                <td>${registro.año}</td>
                <td>${registro.seccion}</td>
                <td>${registro.entrada || '--'}</td>
                <td>${registro.salida || '--'}</td>
             `;
            tbody.appendChild(tr);
        });
    
        // Inicializar DataTable
        $('#dataTable').DataTable();
    });
    </script>

                        </div>
                    
                    <script>
                            const iframe = document.getElementById('miIframe');
                            iframe.onload = function() {
                                iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
                            }
                    </script>
                    </div> 
                    
                </div>
                    <!-- Content Row -->

                    <div class="row">
                        
                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Flujo de Personas</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Seleccionar Año:</div>
                                            <a class="dropdown-item" href="#" onclick="showAttendance(1)">Año 1</a>
                                            <a class="dropdown-item" href="#" onclick="showAttendance(2)">Año 2</a>
                                            <a class="dropdown-item" href="#" onclick="showAttendance(3)">Año 3</a>
                                            <a class="dropdown-item" href="#" onclick="showAttendance(4)">Año 4</a>
                                            <a class="dropdown-item" href="#" onclick="showAttendance(5)">Año 5</a>
                                            <a class="dropdown-item" href="#" onclick="showAttendance(6)">Año 6</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div id="attendanceOverview" class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Asistencia por Género</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Filtrar por Año:</div>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio(1)">1 año</a>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio(2)">2 año</a>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio(3)">3 año</a>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio(4)">4 año</a>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio(5)">5 año</a>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio(6)">6 año</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" onclick="filtrarPorAnio('all')">General</a>

                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Niños
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Niñas
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                                 
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EduTrack 2025   Todos los derechos reservados</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

    <!-- Script para mostrar el flujo de personas -->
    <script>
        function showAttendance(year) {
            const maxAttendance = 235;
            const attendance = Math.floor(Math.random() * maxAttendance);
            const ctx = document.getElementById('myAreaChart').getContext('2d');
            const myAreaChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                    datasets: [{
                        label: `Flujo de personas para el año ${year}`,
                        data: [attendance, attendance - 10, attendance - 20, attendance - 15, attendance - 5],
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: 'rgba(78, 115, 223, 1)',
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'date'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                // Include a dollar sign in the ticks
                                callback: function(value, index, values) {
                                    return number_format(value);
                                }
                            },
                            gridLines: {
                                color: 'rgb(234, 236, 244)',
                                zeroLineColor: 'rgb(234, 236, 244)',
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: 'rgb(255,255,255)',
                        bodyFontColor: '#858796',
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
                            }
                        }
                    }
                }
            });
        }

        function number_format(number, decimals, dec_point, thousands_sep) {
            // *     example: number_format(1234.56, 2, ',', ' ');
            // *     return: '1 234,56'
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs