

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
async function loadData() {
    const response = await fetch("?api=true");
    const data = await response.json();

    // 1️⃣ User Roles
    new Chart(document.getElementById('userChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(data.users),
            datasets: [{
                label: 'Users',
                data: Object.values(data.users),
                backgroundColor: ['#ff6384','#36a2eb','#ffcd56']
            }]
        },
        options: { responsive: true }
    });

    // 2️⃣ Books per Shelf
    new Chart(document.getElementById('bookChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(data.books),
            datasets: [{
                label: 'Books',
                data: Object.values(data.books),
                backgroundColor: '#4bc0c0'
            }]
        },
        options: { responsive: true }
    });

    // 3️⃣ Book Availability
    new Chart(document.getElementById('availabilityChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(data.availability),
            datasets: [{
                data: Object.values(data.availability),
                backgroundColor: ['#36a2eb', '#ff9f40']
            }]
        },
        options: { responsive: true }
    });

    // 4️⃣ Overdue Fines Trend
    new Chart(document.getElementById('overdueChart'), {
        type: 'line',
        data: {
            labels: data.overdue.labels.length > 0 ? data.overdue.labels : ['No Data'],
            datasets: [{
                label: 'Total Fines (₱)',
                data: data.overdue.totals.length > 0 ? data.overdue.totals : [0],
                borderColor: '#ff6384',
                backgroundColor: 'rgba(255,99,132,0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true }
    });
}
loadData();
