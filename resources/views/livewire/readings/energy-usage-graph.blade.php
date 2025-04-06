<div>

    {{-- graph --}}
    <div class="w-full h-64">
        <canvas id="energyUsageGraph{{ $socket->id }}"></canvas>
    </div>

    @once
        @assets
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @endassets
    @endonce


    <script>
        document.addEventListener('DOMContentLoaded',
            function() {
                const ctx = document.getElementById('energyUsageGraph{{ $socket->id }}').getContext('2d');
                const energyUsageGraph = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($labels),
                        datasets: [{
                            label: 'Energy Usage (kWh)',
                            data: @json($data),
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            });
    </script>
</div>
