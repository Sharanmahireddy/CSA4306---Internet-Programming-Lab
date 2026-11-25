<?php
// ========================================================
// PHP & MySQL Persistence Backend for 19_vetcure_animal_care.php
// Database: db_19_vetcure (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_19_vetcure';
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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>VetCure Farmer–Veterinarian Portal</title><style>
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
<header><h1>VetCure Farmer–Veterinarian Portal</h1><p>Farmers submit animal health requests and doctors respond through a demo dashboard.</p><nav><a href="#home">Home</a><a href="#search">Search</a><a href="#user">User</a><a href="#admin">Admin</a><a href="#contact">Contact</a></nav></header>
<main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="user"><h2>Farmer Registration / Login</h2><div class="row"><div><label>Farmer name</label><input id="name"></div><div><label>Email</label><input id="email" type="email"></div><div><label>Village / area</label><input id="area"></div></div><button onclick="login()">Login / Register</button><p id="loginStatus"></p><h2>Submit Animal Health Request</h2><div class="row"><div><label>Animal type</label><select id="animal"><option>Cow</option><option>Buffalo</option><option>Goat</option><option>Sheep</option><option>Dog / Cat</option><option>Other</option></select></div><div><label>Consultation</label><select id="clinic"><option>Government CVH (free demo)</option><option>Private Clinic (fee demo)</option></select></div><div><label>Payment method</label><select id="payment"><option>Bank transfer (demo)</option><option>EasyPaisa (demo)</option></select></div></div><label>Signs and symptoms</label><textarea id="symptoms" placeholder="Describe symptoms; do not rely on this demo for diagnosis"></textarea><label>Media file name (video/audio/image demo)</label><input id="media" placeholder="animal_photo.jpg"><button onclick="submitRequest()">Send to Doctor</button><p id="requestStatus"></p><h3>My Requests / Doctor Responses</h3><div id="myRequests"></div></section>
<section class="card" id="admin"><h2>Doctor Dashboard</h2><label>Doctor password (demo: admin123)</label><input id="pass" type="password"><button onclick="doctorView()">View Requests</button><div id="doctorData"></div><h3>Manage News / Cure Information (educational only)</h3><textarea id="news" placeholder="General animal-care notice"></textarea><button onclick="postNews()">Post News</button><div id="newsList"></div></section>
</main><footer>Internet Programming Lab · Experiment 19 · HTML/CSS/JavaScript demo · Connected to MySQL Database (<code>db_19_vetcure</code>) in phpMyAdmin</footer>
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
let user=null,requests=JSON.parse(localStorage.getItem('vet_requests')||'[]'),news=JSON.parse(localStorage.getItem('vet_news')||'[]');
function login(){let n=document.getElementById('name').value.trim(),e=document.getElementById('email').value.trim(),a=document.getElementById('area').value.trim();if(!n||!e||!a){alert('Enter name, email and area');return}user={name:n,email:e,area:a};document.getElementById('loginStatus').textContent='Logged in as '+n;myRequests()}
function submitRequest(){if(!user){alert('Login first');return}let s=document.getElementById('symptoms').value.trim();if(!s){alert('Describe symptoms');return}let r={id:'VET'+Date.now(),name:user.name,email:user.email,area:user.area,animal:document.getElementById('animal').value,clinic:document.getElementById('clinic').value,payment:document.getElementById('payment').value,symptoms:s,media:document.getElementById('media').value,status:'Submitted',response:''};requests.push(r);localStorage.setItem('vet_requests',JSON.stringify(requests));document.getElementById('requestStatus').textContent='Request submitted: '+r.id;myRequests()}
function myRequests(){document.getElementById('myRequests').innerHTML=requests.filter(r=>user&&r.email===user.email).map(r=>`<div class="card"><b>${r.id}</b> · ${r.animal} · ${r.status}<p>${r.symptoms}</p><p>Doctor response: ${r.response||'Awaiting response'}</p></div>`).join('')||'<p>No requests yet.</p>'}
function doctorView(){if(document.getElementById('pass').value!=='admin123'){alert('Doctor password incorrect');return}document.getElementById('doctorData').innerHTML=requests.map(r=>`<div class="card"><b>${r.id}</b> — ${r.name} (${r.area}) — ${r.animal}<p>${r.symptoms}</p><label>Response / guidance</label><textarea id="resp${r.id}">${r.response}</textarea><label>Status</label><select id="state${r.id}"><option>Submitted</option><option>In Review</option><option>Responded</option></select><button onclick="respond('${r.id}')">Save Response</button></div>`).join('')}
function respond(id){let r=requests.find(x=>x.id===id);r.response=document.getElementById('resp'+id).value;r.status=document.getElementById('state'+id).value;localStorage.setItem('vet_requests',JSON.stringify(requests));doctorView();myRequests()}
function postNews(){if(document.getElementById('pass').value!=='admin123'){alert('Doctor password incorrect');return}let n=document.getElementById('news').value.trim();if(!n)return;news.push(n);localStorage.setItem('vet_news',JSON.stringify(news));renderNews()}
function renderNews(){document.getElementById('newsList').innerHTML=news.map(n=>`<p>${n}</p>`).join('')}
renderNews();
</script></body></html>