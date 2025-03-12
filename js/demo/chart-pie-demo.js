// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';


function filtrarPorAnio(anio) {
  fetch(`php/controllers/get_estadisticas.php?anio=${anio}`)
  .then(response => response.json())
  .then(data => {
      myPieChart.data.datasets[0].data = [data.genero.ninos, data.genero.ninas];
      myPieChart.update();
  });
}

// Actualizar leyenda con colores correctos
document.querySelector('.text-primary').nextElementSibling.innerHTML = 
  '<i class="fas fa-circle text-primary"></i> Niños';
document.querySelector('.text-success').nextElementSibling.innerHTML = 
  '<i class="fas fa-circle text-pink"></i> Niñas'; // Cambiar color a rosa

var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["Niños", "Niñas"],
    datasets: [{
      data: [<?php echo $data['ninos']; ?>, <?php echo $data['ninas']; ?>],
      backgroundColor: ['#4e73df', '#e83e8c'],
      hoverBackgroundColor: ['#2e59d9', '#d63384'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
  },
});