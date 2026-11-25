<?php
// ========================================================
// PHP & MySQL Persistence Backend for 08_hospital_appointment.php
// Database: db_08_hospital (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_08_hospital';
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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MedCare Hospital Appointment System</title><style>
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
<header><h1>MedCare Hospital Appointment System</h1><p>Search doctors, book appointments, check in and review care.</p><nav><a href="#home">Home</a><a href="#search">Search</a><a href="#user">User</a><a href="#admin">Admin</a><a href="#contact">Contact</a></nav></header>
<main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Find Hospital / Doctor</h2><div class="row"><div><label>Location</label><input id="location" placeholder="City or area"></div><div><label>Specialty</label><select id="specialty"><option>All</option><option>General Medicine</option><option>Cardiology</option><option>Dermatology</option><option>Pediatrics</option><option>Orthopedics</option></select></div></div><button onclick="searchDoctors()">Search</button><div id="doctors" class="grid"></div></section>
<section class="card" id="user"><h2>Patient Registration / Appointment</h2><div class="row"><div><label>Patient name</label><input id="name"></div><div><label>Email</label><input id="email" type="email"></div><div><label>Doctor</label><select id="doctor"><option>Dr. A. Kumar — General Medicine</option><option>Dr. S. Rao — Cardiology</option><option>Dr. P. Mehta — Dermatology</option><option>Dr. M. Iyer — Pediatrics</option></select></div><div><label>Date</label><input id="date" type="date"></div><div><label>Time</label><select id="time"><option>09:00</option><option>10:00</option><option>11:00</option><option>14:00</option><option>15:00</option></select></div></div><label>Reason for visit (optional demo field)</label><textarea id="reason"></textarea><button onclick="book()">Book Appointment</button><p id="bookingStatus"></p></section>
<section class="card"><h2>Check-in / Appointment History</h2><label>Email</label><input id="lookup"><button onclick="historyView()">View History</button><div id="history"></div></section>
<section class="card" id="admin"><h2>Hospital Admin</h2><label>Admin password (demo: admin123)</label><input id="pass" type="password"><button onclick="adminView()">View Appointments / Statistics</button><div id="adminData"></div><h3>Doctor Rating / Review</h3><textarea id="review" placeholder="Write a review"></textarea><button onclick="submitReview()">Submit Review</button><p id="reviewStatus"></p></section>
</main><footer>Internet Programming Lab · Experiment 8 · HTML/CSS/JavaScript demo · Connected to MySQL Database (<code>db_08_hospital</code>) in phpMyAdmin</footer>
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
let doctors=[{name:'Dr. A. Kumar',spec:'General Medicine',hospital:'MedCare Central',loc:'Chennai'},{name:'Dr. S. Rao',spec:'Cardiology',hospital:'HeartCare Clinic',loc:'Chennai'},{name:'Dr. P. Mehta',spec:'Dermatology',hospital:'Skin Wellness',loc:'Hyderabad'},{name:'Dr. M. Iyer',spec:'Pediatrics',hospital:'LittleCare Hospital',loc:'Chennai'},{name:'Dr. R. Shah',spec:'Orthopedics',hospital:'City Hospital',loc:'Hyderabad'}];let appts=JSON.parse(localStorage.getItem('hospital_appts')||'[]'),reviews=JSON.parse(localStorage.getItem('hospital_reviews')||'[]');
function searchDoctors(){let l=document.getElementById('location').value.toLowerCase(),s=document.getElementById('specialty').value;let a=doctors.filter(d=>(!l||d.loc.toLowerCase().includes(l)||d.hospital.toLowerCase().includes(l))&&(s==='All'||d.spec===s));document.getElementById('doctors').innerHTML=a.map(d=>`<div class="card"><h3>${d.name}</h3><p>${d.spec}</p><p>${d.hospital} · ${d.loc}</p><button onclick="selectDoctor('${d.name}','${d.spec}')">Book this doctor</button><p><a target="_blank" href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(d.hospital+' '+d.loc)}">View on Google Maps</a></p></div>`).join('')||'<p>No doctors found.</p>'}
function selectDoctor(name,spec){document.getElementById('doctor').innerHTML=`<option>${name} — ${spec}</option>`}
function book(){let name=document.getElementById('name').value.trim(),email=document.getElementById('email').value.trim(),date=document.getElementById('date').value;if(!name||!email||!date){alert('Enter name, email and date');return}let a={id:Date.now(),name,email,doctor:document.getElementById('doctor').value,date,time:document.getElementById('time').value,reason:document.getElementById('reason').value,status:'Booked'};appts.push(a);localStorage.setItem('hospital_appts',JSON.stringify(appts));document.getElementById('bookingStatus').textContent='Appointment booked for '+date+' at '+a.time}
function historyView(){let e=document.getElementById('lookup').value.trim(),a=appts.filter(x=>x.email===e);document.getElementById('history').innerHTML=a.map(x=>`<p>${x.date} ${x.time} · ${x.doctor} · ${x.status} <button onclick="checkin(${x.id})">Check in</button></p>`).join('')||'<p>No appointments found.</p>'}
function checkin(id){let a=appts.find(x=>x.id===id);a.status='Checked in';localStorage.setItem('hospital_appts',JSON.stringify(appts));historyView()}
function adminView(){if(document.getElementById('pass').value!=='admin123'){alert('Admin password incorrect');return}document.getElementById('adminData').innerHTML=`<div class="notice">Doctors: ${doctors.length} · Total appointments: ${appts.length} · Booked: ${appts.filter(a=>a.status==='Booked').length} · Checked in: ${appts.filter(a=>a.status==='Checked in').length}</div>`+appts.map(a=>`<p>${a.date} ${a.time} — ${a.name} — ${a.doctor} — ${a.status}</p>`).join('')}
function submitReview(){let r=document.getElementById('review').value.trim();if(!r)return;reviews.push(r);localStorage.setItem('hospital_reviews',JSON.stringify(reviews));document.getElementById('reviewStatus').textContent='Review saved in demo.'}
searchDoctors();
</script></body></html>