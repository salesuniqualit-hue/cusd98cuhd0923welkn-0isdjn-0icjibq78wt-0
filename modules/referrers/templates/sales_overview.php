<?php
// modules/reports/templates/sales_overview.php
// The $sales_data variable is passed from the routes file.
?>
<div class="page-header">
    <h1>Sales Overview</h1>
    <a href="<?php echo url('/reports'); ?>" class="btn btn-secondary">Back to Reports List</a>
</div>

<div class="card">
    <div class="card-body" style="height: 400px;">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<script src="<?php echo url('/assets/js/Chart.min.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($sales_data ?? [])); ?>,
            datasets: [{
                label: 'Monthly Sales',
                data: <?php echo json_encode(array_values($sales_data ?? [])); ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>