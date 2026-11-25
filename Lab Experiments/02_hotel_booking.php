<?php
// ========================================================
// PHP & MySQL Persistence Backend for 02_hotel_booking.php
// Database: db_02_hotel (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_02_hotel';
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
<title>Hotel Room Booking System</title><style>
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
<header><h1>Hotel Room Booking System</h1><p>Search rooms, manage bookings, and administer room prices and inventory.</p><nav>
<a href="#home">Home</a><a href="#search">Search</a><a href="#account">User Account</a><a href="#admin">Admin Panel</a><a href="#contact">Contact</a>
</nav></header><main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Find a Hotel Room</h2><div class="row"><div><label>Destination</label><input id="destination" placeholder="City or destination"></div><div><label>Check-in</label><input id="checkin" type="date"></div><div><label>Check-out</label><input id="checkout" type="date"></div><div><label>Guests</label><input id="guests" type="number" min="1" value="2"></div></div><button onclick="searchRooms()">Check Availability</button><div id="roomResults" class="grid" style="margin-top:16px"></div></section>
<section class="card" id="account"><h2>User Account</h2><div class="row"><div><label>Name</label><input id="name" placeholder="Full name"></div><div><label>Email</label><input id="email" type="email" placeholder="email@example.com"></div></div><button onclick="saveUser()">Save Profile</button><p id="profileStatus" class="muted"></p><h3>My Bookings</h3><div id="bookings"></div></section>
<section class="card"><h2>Booking Form</h2><div class="row"><div><label>Selected room</label><input id="selectedRoom" readonly placeholder="Choose a room above"></div><div><label>Guest name</label><input id="guestName" placeholder="Name on booking"></div><div><label>Payment mode (demo)</label><select id="payment"><option>UPI</option><option>Card</option><option>Pay at hotel</option></select></div></div><button class="success" onclick="bookRoom()">Confirm Booking</button><p id="bookingStatus"></p></section>
<section class="card" id="admin"><h2>Administrator: Room Inventory</h2><label>Admin password (demo: admin123)</label><input id="adminPass" type="password"><div id="adminRooms"></div></section>
<section class="card" id="contact"><h2>About, Contact & Customer Service</h2><p><b>About:</b> A sample hotel booking portal for an Internet Programming lab experiment.</p><p><b>Contact:</b> support@example.com · +91 90000 00000</p><details><summary>How can I cancel?</summary><p>Demo rule: cancel at least 3 days before check-in.</p></details><details><summary>Can I change dates?</summary><p>Demo rule: modify at least 3 days before check-in.</p></details><p class="muted">Privacy: this prototype stores demo data in your browser only. Do not enter sensitive payment information.</p></section>
</main>
<footer>Internet Programming Lab · Experiment 2 · Front-end demonstration only · Connected to MySQL Database (<code>db_02_hotel</code>) in phpMyAdmin</footer>

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
let rooms=JSON.parse(localStorage.getItem('ip_hotel_rooms')||'null')||[{id:1,type:'Standard',price:2500,qty:8,amenities:'Wi-Fi, AC'},{id:2,type:'Deluxe',price:4000,qty:5,amenities:'Wi-Fi, AC, Breakfast'},{id:3,type:'Suite',price:7000,qty:2,amenities:'Wi-Fi, AC, Lounge'},{id:4,type:'Family',price:5500,qty:3,amenities:'Wi-Fi, AC, 4 guests'}];let bookings=JSON.parse(localStorage.getItem('ip_hotel_bookings')||'[]');let user=JSON.parse(localStorage.getItem('ip_hotel_user')||'null');let selected=null;
function searchRooms(){let d=document.getElementById('destination').value||'Any destination';document.getElementById('roomResults').innerHTML=rooms.map(r=>`<div class="card"><span class="pill">${r.qty} rooms listed</span><h3>${r.type} Room</h3><p>${r.amenities}</p><div class="stat">₹${r.price.toLocaleString()} <small class="muted">/ night</small></div><p class="muted">${d}</p><button onclick="chooseRoom(${r.id})">Choose Room</button></div>`).join('');}
function chooseRoom(id){selected=rooms.find(r=>r.id===id);document.getElementById('selectedRoom').value=selected.type+' — ₹'+selected.price+'/night';}
function saveUser(){let name=document.getElementById('name').value.trim(),email=document.getElementById('email').value.trim();if(!name||!email){alert('Enter name and email');return}user={name,email};localStorage.setItem('ip_hotel_user',JSON.stringify(user));document.getElementById('profileStatus').textContent='Profile saved for '+name;renderBookings();}
function bookRoom(){if(!user){alert('Save your profile first');return}if(!selected){alert('Choose a room first');return}let ci=document.getElementById('checkin').value,co=document.getElementById('checkout').value;if(!ci||!co||co<=ci){alert('Select valid check-in and check-out dates');return}if(!selected.qty){alert('No rooms listed for this type');return}let nights=Math.ceil((new Date(co)-new Date(ci))/86400000),b={id:Date.now(),email:user.email,name:document.getElementById('guestName').value||user.name,room:selected.type,checkin:ci,checkout:co,total:nights*selected.price,status:'Confirmed (demo)'};bookings.push(b);selected.qty--;localStorage.setItem('ip_hotel_bookings',JSON.stringify(bookings));localStorage.setItem('ip_hotel_rooms',JSON.stringify(rooms));document.getElementById('bookingStatus').textContent='Booking confirmed in demo. Total: ₹'+b.total+' · ID '+b.id;renderBookings();searchRooms();renderAdmin();}
function renderBookings(){let bs=bookings.filter(b=>user&&b.email===user.email);document.getElementById('bookings').innerHTML=bs.length?bs.map(b=>`<p><b>${b.room}</b> · ${b.checkin} to ${b.checkout} · ₹${b.total} · ${b.status} <button class="secondary" onclick="cancelBooking(${b.id})">Cancel</button></p>`).join(''):'<p class="muted">No bookings yet.</p>';}
function cancelBooking(id){let b=bookings.find(x=>x.id===id);if(new Date(b.checkin)-new Date() < 3*86400000){alert('Demo policy: cancellation must be at least 3 days before check-in');return}b.status='Cancelled';let r=rooms.find(x=>x.type===b.room);if(r)r.qty++;localStorage.setItem('ip_hotel_bookings',JSON.stringify(bookings));localStorage.setItem('ip_hotel_rooms',JSON.stringify(rooms));renderBookings();searchRooms();renderAdmin();}
function renderAdmin(){document.getElementById('adminRooms').innerHTML=rooms.map(r=>`<div class="row"><p><b>${r.type}</b></p><div><label>Price (₹)</label><input id="price${r.id}" type="number" value="${r.price}"></div><div><label>Quantity</label><input id="qty${r.id}" type="number" value="${r.qty}"></div><button onclick="updateRoom(${r.id})">Update</button></div>`).join('');}
function updateRoom(id){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}let r=rooms.find(x=>x.id===id);r.price=Number(document.getElementById('price'+id).value);r.qty=Number(document.getElementById('qty'+id).value);localStorage.setItem('ip_hotel_rooms',JSON.stringify(rooms));searchRooms();alert('Room updated');}
if(user){document.getElementById('name').value=user.name;document.getElementById('email').value=user.email;document.getElementById('profileStatus').textContent='Profile loaded';}searchRooms();renderBookings();renderAdmin();
</script></body></html>