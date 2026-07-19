document.addEventListener("DOMContentLoaded", function() {
    
    // ==========================================
    // 1. RENDER DOUGHNUT CHART (KOMPOSISI BELANJA)
    // ==========================================
    const donutCanvas = document.getElementById('donutChart');
    if (donutCanvas && typeof dataKategori !== 'undefined' && dataKategori.length > 0) {
        
        const labels = dataKategori.map(item => item.kategori);
        const totals = dataKategori.map(item => item.total);
        
        // Palet warna modern & menarik
        const bgColors = [
            '#1a56db', '#10b981', '#f59e0b', '#ef4444', 
            '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'
        ];

        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: totals,
                    backgroundColor: bgColors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { family: 'Inter', size: 11, weight: '600' },
                            color: '#334155',
                            padding: 15,
                            boxWidth: 12,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return ' ' + label + ': Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }

    // ==========================================
    // 2. RENDER BAR CHART (TREN ARUS KAS 6 BULAN)
    // ==========================================
    const barCanvas = document.getElementById('barChart');
    if (barCanvas && typeof data6Bulan !== 'undefined') {
        
        new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: data6Bulan.labels,
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: data6Bulan.pemasukan,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Pengeluaran',
                        data: data6Bulan.pengeluaran,
                        backgroundColor: '#ef4444',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            font: { family: 'Inter', size: 11, weight: '600' },
                            color: '#334155',
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', weight: '600' }, color: '#64748b' }
                    },
                    y: {
                        border: { dash: [5, 5] },
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Inter', size: 10 },
                            color: '#64748b',
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + 'Jt';
                                } else if (value >= 1000) {
                                    return (value / 1000) + 'Rb';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

});