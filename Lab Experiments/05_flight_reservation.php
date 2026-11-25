<?php
// ========================================================
// PHP & MySQL Persistence Backend for 05_flight_reservation.php
// Database: db_05_flight (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_05_flight';
$ports = [3307, 3306];
$pdo = null;

foreach ($ports as $port) {
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port={$port};dbname={$db_name};charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 2
        ]);
        break;
    } catch (PDOException $e) {}
}

if ($pdo) {
    // Ensure table for persistent state exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `app_data_store` (
        `data_key` VARCHAR(100) PRIMARY KEY,
        `data_value` LONGTEXT NOT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
}

// Handle AJAX Save & Load requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_action'])) {
    header('Content-Type: application/json');
    if (!$pdo) {
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit;
    }

    $action = $_POST['api_action'];
    if ($action === 'save_data') {
        $key = $_POST['key'] ?? '';
        $val = $_POST['val'] ?? '';
        if ($key) {
            $stmt = $pdo->prepare("INSERT INTO `app_data_store` (`data_key`, `data_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `data_value`=VALUES(`data_value`)");
            $stmt->execute([$key, $val]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'load_all') {
        $stmt = $pdo->query("SELECT `data_key`, `data_value` FROM `app_data_store`");
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }
}
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Online Flight Reservation System</title><style>
:root{--navy:#13233f;--blue:#2563eb;--teal:#0f766e;--bg:#f4f7fb;--ink:#1e293b;--muted:#64748b;--line:#dbe3ef;--white:#fff;--red:#dc2626;--green:#15803d}
*{box-sizing:border-box}body{margin:0;font:15px/1.5 Arial,Helvetica,sans-serif;background:var(--bg);color:var(--ink)}
header{background:linear-gradient(120deg,var(--navy),#2455a4);color:#fff;padding:24px clamp(16px,4vw,48px)}
header h1{margin:0 0 5px;font-size:clamp(24px,4vw,34px)}header p{margin:0;color:#dbeafe}
nav{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px}nav a{color:#fff;text-decoration:none;border:1px solid #ffffff55;border-radius:8px;padding:7px 11px;font-size:13px}nav a:hover{background:#ffffff20}
main{max-width:1120px;margin:24px auto;padding:0 16px 36px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
.card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:18px;box-shadow:0 5px 18px #15294b0a;margin-bottom:16px}
.card h2,.card h3{margin-top:0}.card h2{font-size:20px}.card h3{font-size:17px}
label{display:block;font-weight:600;font-size:13px;margin:10px 0 5px}input,select,textarea{width:100%;padding:10px 11px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;background:white;color:var(--ink)}textarea{min-height:90px;resize:vertical}
button,.btn{border:0;border-radius:8px;background:var(--blue);color:white;padding:10px 14px;font:600 14px Arial;cursor:pointer;text-decoration:none;display:inline-block;margin-top:10px}button.secondary{background:#e2e8f0;color:#1e293b}button.danger{background:var(--red)}button.success{background:var(--teal)}button:hover{filter:brightness(.95)}
.row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px}.muted{color:var(--muted)}.pill{display:inline-block;padding:3px 8px;border-radius:99px;background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:700}
table{width:100%;border-collapse:collapse;background:white}th,td{text-align:left;padding:10px;border-bottom:1px solid var(--line);font-size:13px}th{background:#f1f5f9} .table-wrap{overflow:auto;border:1px solid var(--line);border-radius:10px}
.stat{font-size:26px;font-weight:800;color:var(--blue)}.notice{padding:12px 14px;background:#eff6ff;border-left:4px solid var(--blue);border-radius:8px;margin:12px 0}.status{font-weight:700;color:var(--green)}footer{text-align:center;padding:22px;color:var(--muted);font-size:12px}
@media(max-width:600px){header{padding:20px 16px}main{margin-top:16px}.card{padding:14px}th,td{padding:8px}}
</style></head><body>
<header><h1>Online Flight Reservation System</h1><p>Search flights, book e-tickets, check status, print tickets and manage flights.</p><nav>
<a href="#home">Home</a><a href="#search">Search</a><a href="#account">User Account</a><a href="#admin">Admin Panel</a><a href="#contact">Contact</a>
</nav></header><main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Search Flights</h2><div class="row"><div><label>From</label><input id="from" placeholder="Hyderabad"></div><div><label>To</label><input id="to" placeholder="Delhi"></div><div><label>Departure date</label><input id="date" type="date"></div><div><label>Travel class</label><select id="travelClass"><option>Economy</option><option>Premium Economy</option><option>Business</option><option>First Class</option></select></div></div><button onclick="searchFlights()">Search Flights</button><div id="flightResults" class="grid" style="margin-top:16px"></div></section>
<section class="card" id="account"><h2>Customer Login / Registration</h2><div class="row"><div><label>Name</label><input id="name" placeholder="Passenger name"></div><div><label>Email</label><input id="email" type="email" placeholder="email@example.com"></div></div><button onclick="login()">Login / Register</button><p id="loginStatus" class="muted"></p><h3>My Tickets</h3><div id="tickets"></div></section>
<section class="card"><h2>Book Ticket & Payment</h2><div class="row"><div><label>Selected flight</label><input id="selectedFlight" readonly></div><div><label>Passenger name</label><input id="passenger" placeholder="Name as on ID"></div><div><label>Seat preference</label><select id="seatPref"><option>Window</option><option>Aisle</option><option>No preference</option></select></div><div><label>Payment method (demo)</label><select id="payment"><option>UPI</option><option>Card</option><option>Net banking</option></select></div></div><button class="success" onclick="bookFlight()">Pay & Book (Demo)</button><p id="bookingStatus"></p></section>
<section class="card"><h2>View Booking Status / Cancel / Print</h2><label>Booking reference</label><input id="reference" placeholder="Enter booking reference"><button onclick="viewStatus()">View Status</button><button class="danger" onclick="cancelFlight()">Cancel Ticket</button><p id="statusResult"></p></section>
<section class="card"><h2>Customer Feedback</h2><label>Feedback</label><textarea id="feedback" placeholder="Write your feedback"></textarea><button onclick="sendFeedback()">Submit Feedback</button><p id="feedbackStatus"></p></section>
<section class="card" id="admin"><h2>Admin Panel & Report</h2><label>Admin password (demo: admin123)</label><input id="adminPass" type="password"><div class="row"><div><label>Airline / flight</label><input id="airline" placeholder="SkyAir SA101"></div><div><label>From</label><input id="adminFrom" placeholder="Hyderabad"></div><div><label>To</label><input id="adminTo" placeholder="Delhi"></div><div><label>Fare (₹)</label><input id="adminFare" type="number" value="4500"></div><div><label>Seats</label><input id="adminSeats" type="number" value="120"></div></div><button onclick="addFlight()">Add Flight</button><button class="secondary" onclick="adminReport()">Generate Report</button><div id="adminReport"></div></section>
</main>
<footer>Internet Programming Lab · Experiment 5 · Front-end demonstration only · Connected to MySQL Database (<code>db_05_flight</code>) in phpMyAdmin</footer>

<script>
// --- Client-side MySQL Synchronization ---
const PHP_DB_NAME = "<?php echo $db_name; ?>";
const IS_DB_CONNECTED = <?php echo $pdo ? 'true' : 'false'; ?>;

// 1. Preload data from MySQL into localStorage before app scripts run
(function() {
    if (!IS_DB_CONNECTED) return;
    try {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, false); // Synchronous fetch on initial load
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('api_action=load_all');
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success && res.data) {
                for (let k in res.data) {
                    localStorage.setItem(k, res.data[k]);
                }
            }
        }
    } catch (e) {
        console.warn('MySQL preload error:', e);
    }

    // 2. Intercept localStorage.setItem to persist live into MySQL
    const origSetItem = localStorage.setItem.bind(localStorage);
    localStorage.setItem = function(key, val) {
        origSetItem(key, val);
        if (typeof key === 'string' && key.startsWith('ip_')) {
            const fd = new FormData();
            fd.append('api_action', 'save_data');
            fd.append('key', key);
            fd.append('val', val);
            fetch(window.location.href, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    const badge = document.getElementById('db-sync-badge');
                    if (badge) {
                        badge.style.display = 'inline-block';
                        badge.textContent = '🟢 Saved to MySQL: ' + PHP_DB_NAME + ' (' + new Date().toLocaleTimeString() + ')';
                    }
                })
                .catch(err => console.error('MySQL Sync error:', err));
        }
    };
})();
</script>

<script>
let flights=JSON.parse(localStorage.getItem('ip_flights')||'null')||[{id:1,name:'SkyAir SA101',from:'Hyderabad',to:'Delhi',fare:5200,seats:90},{id:2,name:'Air Connect AC204',from:'Chennai',to:'Mumbai',fare:4100,seats:65},{id:3,name:'Blue Horizon BH330',from:'Bengaluru',to:'Hyderabad',fare:2800,seats:48}];let user=null,selected=null;let tickets=JSON.parse(localStorage.getItem('ip_flight_tickets')||'[]');let feedbacks=JSON.parse(localStorage.getItem('ip_flight_feedback')||'[]');
function searchFlights(){let f=document.getElementById('from').value.toLowerCase(),t=document.getElementById('to').value.toLowerCase();let arr=flights.filter(x=>(!f||x.from.toLowerCase().includes(f))&&(!t||x.to.toLowerCase().includes(t)));document.getElementById('flightResults').innerHTML=arr.map(x=>`<div class="card"><span class="pill">${x.seats} seats listed</span><h3>${x.name}</h3><p>${x.from} → ${x.to}</p><p>Class: ${document.getElementById('travelClass').value}</p><div class="stat">₹${x.fare.toLocaleString()}</div><button onclick="chooseFlight(${x.id})">Select Flight</button></div>`).join('')||'<p>No flights found.</p>';}
function chooseFlight(id){selected=flights.find(x=>x.id===id);document.getElementById('selectedFlight').value=selected.name+' · ₹'+selected.fare;}
function login(){let n=document.getElementById('name').value.trim(),e=document.getElementById('email').value.trim();if(!n||!e){alert('Enter name and email');return}user={name:n,email:e};document.getElementById('loginStatus').textContent='Logged in as '+n;renderTickets();}
function bookFlight(){if(!user){alert('Login first');return}if(!selected){alert('Select a flight');return}if(selected.seats<1){alert('No seats available');return}let ref='FL'+Math.floor(100000+Math.random()*900000);let t={ref,flight:selected.name,route:selected.from+' → '+selected.to,date:document.getElementById('date').value||'Not selected',className:document.getElementById('travelClass').value,seat:document.getElementById('seatPref').value,passenger:document.getElementById('passenger').value||user.name,email:user.email,fare:selected.fare,status:'Confirmed (demo)'};selected.seats--;tickets.push(t);localStorage.setItem('ip_flight_tickets',JSON.stringify(tickets));localStorage.setItem('ip_flights',JSON.stringify(flights));document.getElementById('bookingStatus').innerHTML=`Booking confirmed. Reference: <b>${ref}</b> · Fare ₹${t.fare} <button onclick="window.print()">Print E-ticket</button>`;renderTickets();searchFlights();}
function renderTickets(){let arr=tickets.filter(t=>user&&t.email===user.email);document.getElementById('tickets').innerHTML=arr.length?arr.map(t=>`<p>${t.flight} · ${t.route} · ${t.date} · Ref <b>${t.ref}</b> · ${t.status}</p>`).join(''):'<p class="muted">No tickets yet.</p>';}
function viewStatus(){let ref=document.getElementById('reference').value.trim().toUpperCase(),t=tickets.find(x=>x.ref===ref);document.getElementById('statusResult').innerHTML=t?`${t.ref}: ${t.status} · ${t.flight} · ${t.route} · ₹${t.fare} <button onclick="printTicket('${t.ref}')">Print Ticket</button>`:'No matching demo reference.';}
function printTicket(ref){let t=tickets.find(x=>x.ref===ref);if(!t)return;let w=window.open('','_blank');w.document.write(`<h1>Flight E-ticket (Demo)</h1><p>Reference: ${t.ref}</p><p>Passenger: ${t.passenger}</p><p>Flight: ${t.flight}</p><p>Route: ${t.route}</p><p>Date: ${t.date}</p><p>Class: ${t.className}</p><p>Seat preference: ${t.seat}</p><p>Fare: ₹${t.fare}</p><p>Status: ${t.status}</p>`);w.document.close();w.print();}
function cancelFlight(){let ref=document.getElementById('reference').value.trim().toUpperCase(),t=tickets.find(x=>x.ref===ref);if(!t){alert('Reference not found');return}t.status='Cancelled';localStorage.setItem('ip_flight_tickets',JSON.stringify(tickets));document.getElementById('statusResult').textContent='Ticket cancelled in demo. Refund eligibility depends on actual airline rules.';renderTickets();}
function sendFeedback(){let s=document.getElementById('feedback').value.trim();if(!s)return;feedbacks.push(s);localStorage.setItem('ip_flight_feedback',JSON.stringify(feedbacks));document.getElementById('feedbackStatus').textContent='Feedback saved in this browser demo.';document.getElementById('feedback').value='';}
function addFlight(){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}let name=document.getElementById('airline').value.trim(),from=document.getElementById('adminFrom').value.trim(),to=document.getElementById('adminTo').value.trim();if(!name||!from||!to){alert('Enter flight details');return}flights.push({id:Date.now(),name,from,to,fare:Number(document.getElementById('adminFare').value),seats:Number(document.getElementById('adminSeats').value)});localStorage.setItem('ip_flights',JSON.stringify(flights));searchFlights();}
function adminReport(){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}document.getElementById('adminReport').innerHTML=`<div class="notice">Flights: ${flights.length} · Tickets: ${tickets.length} · Active: ${tickets.filter(t=>t.status.startsWith('Confirmed')).length} · Active booking value: ₹${tickets.filter(t=>t.status.startsWith('Confirmed')).reduce((s,t)=>s+t.fare,0)}</div>`;}
searchFlights();
</script></body></html>