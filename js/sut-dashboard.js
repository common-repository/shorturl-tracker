let dateAbscisse = [];
let dataClick = [];
let dataDesktop = [];
let dataMobile = [];
let dataPurchase = [];
if (typeof dashboardData !== 'undefined') {
    dateTable = dashboardData.dayByDay;
    for (const [key, value] of Object.entries(dateTable)) {
        dateAbscisse.push(key);
        dataClick.push(value.click);
        dataMobile.push(value.device.mobile);
        dataDesktop.push(value.device.desktop);
        dataPurchase.push(value.nb_purchase);
    }
}
if (document.getElementById('dashboardChart')) {
    const ctx = document.getElementById('dashboardChart').getContext('2d');
    const data = {
        labels: dateAbscisse,
        datasets : [{
            label : 'Purchase',
            data: dataPurchase,
            backgroundColor: "#55D5E0",
            order: 2,
            stack: 'purchase'
        },
            {
                label : 'Desktop',
                data: dataDesktop,
                backgroundColor: "#F26619",
                order: 3,
                stack: 'device'
            },
            {
                label : 'Mobile',
                data: dataMobile,
                backgroundColor: "#F6B12D",
                order: 3,
                stack: 'device'
            },
            {
                label: 'Click',
                data: dataClick,
                backgroundColor: '#4d6179',
                borderColor: '#4d6179',
                type: 'line',
                tension: 0.4,
                order: 1
            }
        ]
    }
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'Dashboard ShortUrl Tracker'
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 3.5,
            scales: {
                y: {
                    beginAtZero: true,
                    stacked: true,
                    suggestedMax: Math.max.apply(null,dataClick) + 10
                },
                x: {
                    stacked: true
                }
            }
        },
    });
}

