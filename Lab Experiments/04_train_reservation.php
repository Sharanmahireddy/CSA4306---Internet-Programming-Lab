<?php
// ========================================================
// PHP & MySQL Persistence Backend for 04_train_reservation.php
// Database: db_04_train (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_04_train';
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
<title>Online Train Reservation System</title><style>
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
<header><h1>Online Train Reservation System</h1><p>Search trains, reserve tickets, check demo PNR and manage train data.</p><nav>
<a href="#home">Home</a><a href="#search">Search</a><a href="#account">User Account</a><a href="#admin">Admin Panel</a><a href="#contact">Contact</a>
</nav></header><main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Search Trains</h2><div class="row"><div><label>Origin station</label><input id="origin" placeholder="Chennai"></div><div><label>Destination station</label><input id="destination" placeholder="Bengaluru"></div><div><label>Journey date</label><input id="journeyDate" type="date"></div><div><label>Class</label><select id="classType"><option>Sleeper</option><option>3A</option><option>2A</option><option>1A</option><option>Chair Car</option></select></div></div><button onclick="searchTrains()">Search Trains</button><div id="trainResults" class="grid" style="margin-top:16px"></div></section>
<section class="card" id="account"><h2>Passenger Login / Register</h2><div class="row"><div><label>Name</label><input id="name" placeholder="Passenger name"></div><div><label>Email</label><input id="email" type="email" placeholder="email@example.com"></div></div><button onclick="login()">Login / Register</button><p id="loginStatus" class="muted"></p><h3>My Reservations</h3><div id="reservations"></div></section>
<section class="card"><h2>Passenger Details & Payment</h2><div class="row"><div><label>Selected train</label><input id="selectedTrain" readonly></div><div><label>Passenger full name</label><input id="passengerName"></div><div><label>Payment mode</label><select id="paymentMode"><option>Online (demo)</option><option>Offline counter</option></select></div></div><button class="success" onclick="reserve()">Reserve Ticket</button><p id="ticketResult"></p></section>
<section class="card"><h2>PNR Status / Cancellation</h2><label>PNR number</label><input id="pnrInput" placeholder="Enter demo PNR"><button onclick="checkPNR()">Check PNR</button><button class="danger" onclick="cancelPNR()">Cancel Ticket</button><p id="pnrResult"></p><p class="muted">Refund calculation is illustrative and should be replaced with approved fare and cancellation rules.</p></section>
<section class="card" id="admin"><h2>Admin: Train, Fare & Reports</h2><label>Admin password (demo: admin123)</label><input id="adminPass" type="password"><div class="row"><div><label>Train name</label><input id="trainName" placeholder="Coastal Express"></div><div><label>Origin</label><input id="trainOrigin" placeholder="Chennai"></div><div><label>Destination</label><input id="trainDestination" placeholder="Bengaluru"></div><div><label>Fare (₹)</label><input id="trainFare" type="number" value="500"></div><div><label>Available seats</label><input id="trainSeats" type="number" value="50"></div></div><button onclick="addTrain()">Add Train</button><button class="secondary" onclick="report()">Generate Report</button><div id="report"></div></section>
</main>
<footer>Internet Programming Lab · Experiment 4 · Front-end demonstration only · Connected to MySQL Database (<code>db_04_train</code>) in phpMyAdmin</footer>

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
let trains=JSON.parse(localStorage.getItem('ip_trains')||'null')||[{id:101,name:'Coastal Express',origin:'Chennai',destination:'Bengaluru',fare:520,seats:45},{id:126,name:'Southern Intercity',origin:'Hyderabad',destination:'Vijayawada',fare:390,seats:60},{id:204,name:'Night Mail',origin:'Chennai',destination:'Hyderabad',fare:680,seats:32}];let user=null;let reservations=JSON.parse(localStorage.getItem('ip_train_reservations')||'[]');let selected=null;
function searchTrains(){let o=document.getElementById('origin').value.toLowerCase(),d=document.getElementById('destination').value.toLowerCase();let arr=trains.filter(t=>(!o||t.origin.toLowerCase().includes(o))&&(!d||t.destination.toLowerCase().includes(d)));document.getElementById('trainResults').innerHTML=arr.map(t=>`<div class="card"><span class="pill">${t.seats} seats</span><h3>${t.name}</h3><p>${t.origin} → ${t.destination}</p><p>Class: ${document.getElementById('classType').value}</p><div class="stat">₹${t.fare}</div><button onclick="chooseTrain(${t.id})">Select Train</button></div>`).join('')||'<p>No trains found.</p>';}
function chooseTrain(id){selected=trains.find(t=>t.id===id);document.getElementById('selectedTrain').value=selected.name+' · ₹'+selected.fare;}
function login(){let n=document.getElementById('name').value.trim(),e=document.getElementById('email').value.trim();if(!n||!e){alert('Enter name and email');return}user={name:n,email:e};document.getElementById('loginStatus').textContent='Logged in as '+n;renderReservations();}
function reserve(){if(!user){alert('Login first');return}if(!selected){alert('Select a train');return}if(selected.seats<1){alert('No seats available');return}let pnr=String(Math.floor(1000000000+Math.random()*9000000000));let r={pnr,train:selected.name,route:selected.origin+' → '+selected.destination,date:document.getElementById('journeyDate').value||'Not selected',classType:document.getElementById('classType').value,passenger:document.getElementById('passengerName').value||user.name,email:user.email,fare:selected.fare,status:'Confirmed (demo)'};selected.seats--;reservations.push(r);localStorage.setItem('ip_train_reservations',JSON.stringify(reservations));localStorage.setItem('ip_trains',JSON.stringify(trains));document.getElementById('ticketResult').innerHTML=`Reservation created. Demo PNR: <b>${pnr}</b> · Fare ₹${r.fare} <button onclick="window.print()">Print Ticket</button>`;renderReservations();searchTrains();}
function renderReservations(){let arr=reservations.filter(r=>user&&r.email===user.email);document.getElementById('reservations').innerHTML=arr.length?arr.map(r=>`<p>${r.train} · ${r.route} · ${r.date} · PNR <b>${r.pnr}</b> · ${r.status}</p>`).join(''):'<p class="muted">No reservations yet.</p>';}
function checkPNR(){let p=document.getElementById('pnrInput').value.trim(),r=reservations.find(x=>x.pnr===p);document.getElementById('pnrResult').textContent=r?`PNR ${r.pnr}: ${r.status} · ${r.train} · ${r.route} · Fare ₹${r.fare}`:'No matching demo PNR found.';}
function cancelPNR(){let p=document.getElementById('pnrInput').value.trim(),r=reservations.find(x=>x.pnr===p);if(!r){alert('PNR not found');return}if(r.status==='Cancelled'){alert('Already cancelled');return}r.status='Cancelled';r.refund=Math.round(r.fare*.8);localStorage.setItem('ip_train_reservations',JSON.stringify(reservations));document.getElementById('pnrResult').textContent='Ticket cancelled. Illustrative refund: ₹'+r.refund;renderReservations();}
function addTrain(){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}let name=document.getElementById('trainName').value.trim(),origin=document.getElementById('trainOrigin').value.trim(),destination=document.getElementById('trainDestination').value.trim();if(!name||!origin||!destination){alert('Enter train details');return}trains.push({id:Date.now(),name,origin,destination,fare:Number(document.getElementById('trainFare').value),seats:Number(document.getElementById('trainSeats').value)});localStorage.setItem('ip_trains',JSON.stringify(trains));searchTrains();}
function report(){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}document.getElementById('report').innerHTML=`<div class="notice">Train count: ${trains.length} · Reservations: ${reservations.length} · Active: ${reservations.filter(r=>!r.status.startsWith('Cancelled')).length} · Booked fare total: ₹${reservations.filter(r=>!r.status.startsWith('Cancelled')).reduce((s,r)=>s+r.fare,0)}</div>`;}
searchTrains();
</script></body></html>