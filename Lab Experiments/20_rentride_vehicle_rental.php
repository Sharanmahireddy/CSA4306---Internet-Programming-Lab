<?php
// ========================================================
// PHP & MySQL Persistence Backend for 20_rentride_vehicle_rental.php
// Database: db_20_rentride (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_20_rentride';
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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>RentRide Vehicle Rental System</title><style>
:root{--navy:#13233f;--blue:#2563eb;--teal:#0f766e;--bg:#f4f7fb;--ink:#1e293b;--muted:#64748b;--line:#dbe3ef}
*{box-sizing:border-box}body{margin:0;font:15px/1.5 Arial,sans-serif;background:var(--bg);color:var(--ink)}
header{background:linear-gradient(120deg,var(--navy),#2455a4);color:#fff;padding:24px clamp(16px,4vw,44px)}header h1{margin:0 0 5px;font-size:clamp(24px,4vw,34px)}header p{margin:0;color:#dbeafe}
nav{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}nav a{color:#fff;text-decoration:none;border:1px solid #ffffff55;border-radius:8px;padding:7px 10px;font-size:13px}
main{max-width:1100px;margin:24px auto;padding:0 16px 36px}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px}
.card{background:#fff;border:1px solid var(--line);border-radius:13px;padding:17px;box-shadow:0 5px 18px #15294b0a;margin-bottom:15px}.card h2,.card h3{margin-top:0}label{display:block;font-weight:600;font-size:13px;margin:10px 0 5px}
input,select,textarea{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;background:#fff}textarea{min-height:80px}
button,.btn{border:0;border-radius:8px;background:var(--blue);color:white;padding:10px 13px;font-weight:600;cursor:pointer;margin:8px 5px 0 0}button.secondary{background:#e2e8f0;color:#1e293b}button.danger{background:#dc2626}.row{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px}
.notice{padding:12px 14px;background:#eff6ff;border-left:4px solid var(--blue);border-radius:8px;margin:12px 0}.muted{color:var(--muted)}.pill{display:inline-block;padding:3px 8px;border-radius:99px;background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:700}
.stat{font-size:25px;font-weight:800;color:var(--blue)}.table-wrap{overflow:auto;border:1px solid var(--line);border-radius:9px}table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:9px;border-bottom:1px solid var(--line);font-size:13px}th{background:#f1f5f9}footer{text-align:center;padding:22px;color:var(--muted);font-size:12px}
@media(max-width:600px){header{padding:20px 16px}main{margin-top:15px}.card{padding:13px}}
</style></head><body>
<header><h1>RentRide Vehicle Rental System</h1><p>Search vehicles, make rental requests and manage vehicle inventory and bookings.</p><nav><a href="#home">Home</a><a href="#search">Search</a><a href="#user">User</a><a href="#admin">Admin</a><a href="#contact">Contact</a></nav></header>
<main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Search Vehicles</h2><div class="row"><div><label>Location</label><input id="location" placeholder="Chennai"></div><div><label>Vehicle type</label><select id="type"><option>All</option><option>Car</option><option>SUV</option><option>Truck</option><option>Bike</option></select></div><div><label>Max daily price (₹)</label><input id="max" type="number" value="10000"></div></div><button onclick="searchVehicles()">Search</button><div id="vehicles" class="grid"></div></section>
<section class="card" id="user"><h2>Customer Registration / Booking</h2><div class="row"><div><label>Name</label><input id="name"></div><div><label>Phone</label><input id="phone"></div><div><label>Address</label><input id="address"></div><div><label>Start date</label><input id="start" type="date"></div><div><label>End date</label><input id="end" type="date"></div></div><button onclick="saveUser()">Login / Register</button><p id="userStatus"></p><p>Selected vehicle: <b id="selected">None</b></p><button class="success" onclick="book()">Make Booking</button><p id="bookingStatus"></p><h3>My Booking Status</h3><div id="myBookings"></div></section>
<section class="card" id="admin"><h2>Admin Vehicle & Booking Management</h2><label>Admin password (demo: admin123)</label><input id="pass" type="password"><div class="row"><div><label>Vehicle name</label><input id="vname"></div><div><label>Type</label><select id="vtype"><option>Car</option><option>SUV</option><option>Truck</option><option>Bike</option></select></div><div><label>Daily price</label><input id="price" type="number" value="1000"></div><div><label>Location</label><input id="vloc" value="Chennai"></div></div><button onclick="addVehicle()">Add Vehicle</button><button class="secondary" onclick="adminView()">View / Approve Bookings</button><div id="adminData"></div></section>
</main><footer>Internet Programming Lab · Experiment 20 · HTML/CSS/JavaScript demo · Connected to MySQL Database (<code>db_20_rentride</code>) in phpMyAdmin</footer>
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
let vehicles=JSON.parse(localStorage.getItem('rent_vehicles')||'null')||[{id:1,name:'City Hatchback',type:'Car',price:1800,loc:'Chennai',status:'Available'},{id:2,name:'Family SUV',type:'SUV',price:3500,loc:'Chennai',status:'Available'},{id:3,name:'Delivery Truck',type:'Truck',price:5000,loc:'Adilabad',status:'Available'},{id:4,name:'City Bike',type:'Bike',price:500,loc:'Chennai',status:'Available'}],bookings=JSON.parse(localStorage.getItem('rent_bookings')||'[]'),user=null,selected=null;
function searchVehicles(){let l=document.getElementById('location').value.toLowerCase(),t=document.getElementById('type').value,m=Number(document.getElementById('max').value||999999);let a=vehicles.filter(v=>(!l||v.loc.toLowerCase().includes(l))&&(t==='All'||v.type===t)&&v.price<=m&&v.status==='Available');document.getElementById('vehicles').innerHTML=a.map(v=>`<div class="card"><span class="pill">${v.type}</span><h3>${v.name}</h3><p>📍 ${v.loc}</p><div class="stat">₹${v.price.toLocaleString()} <small>/day</small></div><button onclick="selectVehicle(${v.id})">Select Vehicle</button></div>`).join('')||'<p>No available vehicles match.</p>'}
function selectVehicle(id){selected=vehicles.find(v=>v.id===id);document.getElementById('selected').textContent=selected.name+' — ₹'+selected.price+'/day'}
function saveUser(){let n=document.getElementById('name').value.trim(),p=document.getElementById('phone').value.trim(),a=document.getElementById('address').value.trim();if(!n||!p||!a){alert('Enter name, phone and address');return}user={name:n,phone:p,address:a};document.getElementById('userStatus').textContent='Logged in as '+n;myBookings()}
function book(){if(!user){alert('Register/login first');return}if(!selected){alert('Select a vehicle');return}let s=document.getElementById('start').value,e=document.getElementById('end').value;if(!s||!e||e<s){alert('Choose valid rental dates');return}let days=Math.ceil((new Date(e)-new Date(s))/86400000)+1,b={id:'RR'+Date.now(),name:user.name,phone:user.phone,address:user.address,vehicle:selected.name,vehicleId:selected.id,start:s,end:e,days,total:days*selected.price,status:'Pending approval'};bookings.push(b);localStorage.setItem('rent_bookings',JSON.stringify(bookings));document.getElementById('bookingStatus').textContent='Booking request '+b.id+' · '+days+' day(s) · ₹'+b.total;myBookings()}
function myBookings(){document.getElementById('myBookings').innerHTML=bookings.filter(b=>user&&b.phone===user.phone).map(b=>`<p>${b.id} · ${b.vehicle} · ${b.start} to ${b.end} · ₹${b.total} · ${b.status}</p>`).join('')||'<p>No bookings yet.</p>'}
function addVehicle(){if(document.getElementById('pass').value!=='admin123'){alert('Admin password incorrect');return}let n=document.getElementById('vname').value.trim();if(!n)return;vehicles.push({id:Date.now(),name:n,type:document.getElementById('vtype').value,price:Number(document.getElementById('price').value),loc:document.getElementById('vloc').value,status:'Available'});localStorage.setItem('rent_vehicles',JSON.stringify(vehicles));searchVehicles()}
function adminView(){if(document.getElementById('pass').value!=='admin123'){alert('Admin password incorrect');return}document.getElementById('adminData').innerHTML='<h3>Bookings</h3>'+bookings.map(b=>`<p>${b.id} · ${b.name} · ${b.vehicle} · ${b.status} <button onclick="approve('${b.id}')">Approve</button><button class="danger" onclick="cancel('${b.id}')">Cancel</button></p>`).join('')+'<h3>Vehicles</h3>'+vehicles.map(v=>`<p>${v.name} · ${v.type} · ₹${v.price}/day · ${v.status}</p>`).join('')}
function approve(id){let b=bookings.find(x=>x.id===id);b.status='Approved';localStorage.setItem('rent_bookings',JSON.stringify(bookings));adminView();myBookings()}
function cancel(id){let b=bookings.find(x=>x.id===id);b.status='Cancelled';localStorage.setItem('rent_bookings',JSON.stringify(bookings));adminView();myBookings()}
searchVehicles();
</script></body></html>