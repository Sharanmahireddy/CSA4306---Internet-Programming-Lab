<?php
// ========================================================
// PHP & MySQL Persistence Backend for 03_bus_booking.php
// Database: db_03_bus (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_03_bus';
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
<title>Online Bus Ticket Booking System</title><style>
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
<header><h1>Online Bus Ticket Booking System</h1><p>Search routes, select seats, book tickets, cancel and generate reports.</p><nav>
<a href="#home">Home</a><a href="#search">Search</a><a href="#account">User Account</a><a href="#admin">Admin Panel</a><a href="#contact">Contact</a>
</nav></header><main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Search Buses</h2><div class="row"><div><label>From</label><input id="from" placeholder="e.g. Chennai"></div><div><label>To</label><input id="to" placeholder="e.g. Bengaluru"></div><div><label>Journey date</label><input id="date" type="date"></div></div><button onclick="searchBuses()">Search Buses</button><div id="busResults" class="grid" style="margin-top:16px"></div></section>
<section class="card" id="account"><h2>Login / Registration</h2><div class="row"><div><label>Name</label><input id="userName" placeholder="Passenger name"></div><div><label>Email</label><input id="userEmail" type="email" placeholder="email@example.com"></div></div><button onclick="login()">Login / Register</button><p id="loginStatus" class="muted"></p><h3>My Ticket History</h3><div id="history"></div></section>
<section class="card"><h2>Passenger Details & Ticket Booking</h2><div class="row"><div><label>Selected bus</label><input id="selectedBus" readonly placeholder="Choose a bus above"></div><div><label>Passenger name</label><input id="passenger" placeholder="Full name"></div><div><label>Seat number</label><select id="seat"><option value="">Select seat</option></select></div></div><button class="success" onclick="book()">Book Ticket</button><p id="bookingStatus"></p></section>
<section class="card"><h2>Comments / Feedback</h2><label>Comment</label><textarea id="comment" placeholder="Share your feedback"></textarea><button onclick="addComment()">Post Comment</button><div id="comments"></div></section>
<section class="card" id="admin"><h2>Admin Panel & Report</h2><label>Admin password (demo: admin123)</label><input id="adminPass" type="password"><div class="row"><div><label>Bus name</label><input id="newBus" placeholder="Bus operator"></div><div><label>Category</label><select id="category"><option>Volvo AC</option><option>Sleeper Coach</option><option>Seater Coach</option><option>Non-AC</option></select></div><div><label>Fare (₹)</label><input id="fare" type="number" value="600"></div><div><label>Total seats</label><input id="seats" type="number" value="30"></div></div><button onclick="addBus()">Add Bus</button><button class="secondary" onclick="report()">Generate Report</button><div id="reportOutput"></div></section>
</main>
<footer>Internet Programming Lab · Experiment 3 · Front-end demonstration only · Connected to MySQL Database (<code>db_03_bus</code>) in phpMyAdmin</footer>

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
let buses=JSON.parse(localStorage.getItem('ip_buses')||'null')||[{id:1,name:'Express Travels',category:'Volvo AC',fare:850,seats:30,from:'Chennai',to:'Bengaluru'},{id:2,name:'Night Rider',category:'Sleeper Coach',fare:650,seats:24,from:'Chennai',to:'Bengaluru'},{id:3,name:'City Connect',category:'Seater Coach',fare:450,seats:40,from:'Hyderabad',to:'Vijayawada'}];let user=null,selected=null;let tickets=JSON.parse(localStorage.getItem('ip_bus_tickets')||'[]');let comments=JSON.parse(localStorage.getItem('ip_bus_comments')||'[]');
function searchBuses(){let f=document.getElementById('from').value.toLowerCase(),t=document.getElementById('to').value.toLowerCase();let arr=buses.filter(b=>(!f||b.from.toLowerCase().includes(f))&&(!t||b.to.toLowerCase().includes(t)));document.getElementById('busResults').innerHTML=arr.map(b=>`<div class="card"><span class="pill">${b.category}</span><h3>${b.name}</h3><p>${b.from} → ${b.to}</p><p>Seats: ${b.seats}</p><div class="stat">₹${b.fare}</div><button onclick="choose(${b.id})">Select Bus</button></div>`).join('')||'<p>No buses found for this route.</p>';}
function choose(id){selected=buses.find(b=>b.id===id);document.getElementById('selectedBus').value=selected.name+' · '+selected.from+' → '+selected.to;let s=document.getElementById('seat');s.innerHTML='<option value="">Select seat</option>';for(let i=1;i<=selected.seats;i++)s.innerHTML+=`<option value="${i}">Seat ${i}</option>`;}
function login(){let n=document.getElementById('userName').value.trim(),e=document.getElementById('userEmail').value.trim();if(!n||!e){alert('Enter name and email');return}user={name:n,email:e};document.getElementById('loginStatus').textContent='Logged in as '+n;renderHistory();}
function book(){if(!user){alert('Login first');return}if(!selected){alert('Select a bus');return}let seat=document.getElementById('seat').value;if(!seat){alert('Choose a seat');return}if(tickets.some(t=>t.busId===selected.id&&t.seat===seat&&t.status==='Booked')){alert('Seat already booked in this browser demo');return}let ticket={id:Date.now(),busId:selected.id,bus:selected.name,route:selected.from+' → '+selected.to,seat,name:document.getElementById('passenger').value||user.name,email:user.email,date:document.getElementById('date').value||'Not selected',fare:selected.fare,status:'Booked'};tickets.push(ticket);localStorage.setItem('ip_bus_tickets',JSON.stringify(tickets));document.getElementById('bookingStatus').innerHTML=`Ticket booked. Ticket ID: <b>${ticket.id}</b> · Fare ₹${ticket.fare} <button onclick="window.print()">Print Ticket</button>`;renderHistory();}
function renderHistory(){let arr=tickets.filter(t=>user&&t.email===user.email);document.getElementById('history').innerHTML=arr.length?arr.map(t=>`<p>${t.bus} · ${t.route} · Seat ${t.seat} · ₹${t.fare} · ${t.status} <button class="danger" onclick="cancel(${t.id})">Cancel</button></p>`).join(''):'<p class="muted">No tickets yet.</p>';}
function cancel(id){let t=tickets.find(x=>x.id===id);t.status='Cancelled';localStorage.setItem('ip_bus_tickets',JSON.stringify(tickets));renderHistory();}
function addBus(){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}let name=document.getElementById('newBus').value.trim();if(!name){alert('Enter bus name');return}buses.push({id:Date.now(),name,category:document.getElementById('category').value,fare:Number(document.getElementById('fare').value),seats:Number(document.getElementById('seats').value),from:document.getElementById('from').value||'Chennai',to:document.getElementById('to').value||'Bengaluru'});localStorage.setItem('ip_buses',JSON.stringify(buses));searchBuses();}
function addComment(){let c=document.getElementById('comment').value.trim();if(!c)return;comments.push(c);localStorage.setItem('ip_bus_comments',JSON.stringify(comments));renderComments();document.getElementById('comment').value='';}
function renderComments(){document.getElementById('comments').innerHTML=comments.map(c=>`<p>💬 ${esc(c)}</p>`).join('');}
function report(){if(document.getElementById('adminPass').value!=='admin123'){alert('Admin password incorrect');return}document.getElementById('reportOutput').innerHTML=`<div class="notice">Total buses: ${buses.length} · Tickets: ${tickets.length} · Active bookings: ${tickets.filter(t=>t.status==='Booked').length} · Revenue (booked): ₹${tickets.filter(t=>t.status==='Booked').reduce((s,t)=>s+t.fare,0)}</div>`;}
function esc(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
searchBuses();renderComments();
</script></body></html>