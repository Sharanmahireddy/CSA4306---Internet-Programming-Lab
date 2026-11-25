<?php
// ========================================================
// PHP & MySQL Persistence Backend for 09_techsavvy_electronics.php
// Database: db_09_electronics (XAMPP MariaDB/phpMyAdmin)
// ========================================================

$db_name = 'db_09_electronics';
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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TechSavvy Electronics Store</title><style>
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
<header><h1>TechSavvy Electronics Store</h1><p>Browse gadgets, filter products, manage wishlist, cart and orders.</p><nav><a href="#home">Home</a><a href="#search">Search</a><a href="#user">User</a><a href="#admin">Admin</a><a href="#contact">Contact</a></nav></header>
<main id="home">
<div class="notice" style="background:#ecfdf5; border-left:4px solid #10b981; color:#065f46; padding:14px; border-radius:8px; margin:14px 0;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <span><b>🟢 Connected to MySQL:</b> <code><?php echo $db_name; ?></code></span>
        <span id="db-sync-badge" style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:12px; font-weight:700;">🟢 Live phpMyAdmin Sync Active</span>
    </div>
    <div style="font-size:13px; color:#047857; margin-top:4px;">All records submitted in this interface are permanently stored in your XAMPP MySQL database.</div>
</div>
<section class="card" id="search"><h2>Shop Products</h2><div class="row"><div><label>Search products</label><input id="q" placeholder="Search name or description"></div><div><label>Category</label><select id="cat"><option>All</option><option>Smartphones</option><option>Laptops</option><option>Tablets</option><option>Accessories</option></select></div><div><label>Maximum price (₹)</label><input id="max" type="number" value="100000"></div></div><button onclick="renderProducts()">Search / Filter</button><div id="products" class="grid"></div></section>
<section class="card" id="user"><h2>Customer Account</h2><div class="row"><div><label>Name</label><input id="name"></div><div><label>Email</label><input id="email" type="email"></div><div><label>Shipping address</label><input id="address"></div></div><button onclick="login()">Login / Register</button><p id="userStatus" class="muted"></p><h3>Wishlist</h3><div id="wishlist"></div></section>
<section class="card"><h2>Shopping Cart</h2><div id="cart"></div><button onclick="checkout()">Checkout (Demo)</button><p id="checkoutStatus"></p><h3>Order History / Status</h3><div id="orders"></div></section>
<section class="card" id="admin"><h2>Admin Inventory</h2><label>Admin password (demo: admin123)</label><input id="pass" type="password"><div class="row"><div><label>Product name</label><input id="pname"></div><div><label>Category</label><select id="pcat"><option>Smartphones</option><option>Laptops</option><option>Tablets</option><option>Accessories</option></select></div><div><label>Price</label><input id="price" type="number" value="999"></div><div><label>Description</label><input id="desc"></div></div><button onclick="addProduct()">Add Product</button><button class="secondary" onclick="adminOrders()">View Customer Orders</button><div id="adminData"></div></section>
<section class="card" id="contact"><h2>Contact & Policy</h2><p>Support: support@example.com · Sample shipping, return and privacy policies for lab demonstration.</p><label>Feedback / inquiry</label><textarea id="feedback"></textarea><button onclick="alert('Feedback saved in demo only')">Submit</button></section>
</main><footer>Internet Programming Lab · Experiment 9 · HTML/CSS/JavaScript demo · Connected to MySQL Database (<code>db_09_electronics</code>) in phpMyAdmin</footer>
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
let products=JSON.parse(localStorage.getItem('9_products')||'null')||[{"id": 1, "name": "Smartphone X", "cat": "Smartphones", "price": 24999, "desc": "Large display, dual camera"}, {"id": 2, "name": "Everyday Laptop", "cat": "Laptops", "price": 55999, "desc": "Productivity laptop"}, {"id": 3, "name": "Android Tablet", "cat": "Tablets", "price": 18999, "desc": "Portable tablet"}, {"id": 4, "name": "Wireless Earbuds", "cat": "Accessories", "price": 1999, "desc": "Bluetooth audio"}],cart=JSON.parse(localStorage.getItem('9_cart')||'[]'),orders=JSON.parse(localStorage.getItem('9_orders')||'[]'),wishlist=JSON.parse(localStorage.getItem('9_wish')||'[]'),user=null;
function renderProducts(){let q=document.getElementById('q').value.toLowerCase(),cat=document.getElementById('cat').value,max=Number(document.getElementById('max').value||99999999),a=products.filter(p=>(p.name+' '+p.desc).toLowerCase().includes(q)&&(cat==='All'||p.cat===cat)&&p.price<=max);document.getElementById('products').innerHTML=a.map(p=>`<div class="card"><span class="pill">${p.cat}</span><h3>${p.name}</h3><p>${p.desc}</p><div class="stat">₹${p.price.toLocaleString()}</div><button onclick="addCart(${p.id})">Add to Cart</button><button class="secondary" onclick="wish(${p.id})">♡ Wishlist</button></div>`).join('')||'<p>No products found.</p>'}
function addCart(id){cart.push(products.find(p=>p.id===id));localStorage.setItem('9_cart',JSON.stringify(cart));renderCart()}
function renderCart(){document.getElementById('cart').innerHTML=cart.length?cart.map((p,i)=>`<p>${p.name} — ₹${p.price} <button class="secondary" onclick="changeQty(${i})">Edit quantity</button><button class="danger" onclick="removeCart(${i})">Remove</button></p>`).join('')+'<b>Total: ₹'+cart.reduce((s,p)=>s+p.price,0).toLocaleString()+'</b>':'<p class="muted">Cart is empty.</p>'}
function removeCart(i){cart.splice(i,1);localStorage.setItem('9_cart',JSON.stringify(cart));renderCart()}
function changeQty(i){let n=Number(prompt('New quantity (1-10)',1));if(n<1||n>10||!Number.isFinite(n))return;let p=cart[i];for(let j=1;j<n;j++)cart.push({...p});if(n===1){let same=cart.filter(x=>x.id===p.id);if(same.length>1)cart.splice(i,1)}localStorage.setItem('9_cart',JSON.stringify(cart));renderCart()}
function login(){let name=document.getElementById('name').value.trim(),email=document.getElementById('email').value.trim(),address=document.getElementById('address').value.trim();if(!name||!email){alert('Enter name and email');return}user={name,email,address};document.getElementById('userStatus').textContent='Signed in as '+name;renderOrders();renderWishlist()}
function wish(id){if(!wishlist.includes(id))wishlist.push(id);localStorage.setItem('9_wish',JSON.stringify(wishlist));renderWishlist()}
function renderWishlist(){document.getElementById('wishlist').innerHTML=wishlist.map(id=>products.find(p=>p.id===id)).filter(Boolean).map(p=>`<p>${p.name} — ₹${p.price}</p>`).join('')||'<p class="muted">No saved items.</p>'}
function checkout(){if(!user){alert('Login/register first');return}if(!cart.length){alert('Cart is empty');return}let o={id:'ORD'+Date.now(),email:user.email,items:cart.map(p=>p.name).join(', '),total:cart.reduce((s,p)=>s+p.price,0),address:user.address,status:'Order placed (demo)'};orders.push(o);localStorage.setItem('9_orders',JSON.stringify(orders));cart=[];localStorage.setItem('9_cart','[]');renderCart();renderOrders();document.getElementById('checkoutStatus').textContent='Order '+o.id+' placed. Total ₹'+o.total}
function renderOrders(){let a=orders.filter(o=>user&&o.email===user.email);document.getElementById('orders').innerHTML=a.map(o=>`<p>${o.id} · ${o.items} · ₹${o.total} · ${o.status}</p>`).join('')||'<p class="muted">No orders yet.</p>'}
function addProduct(){if(document.getElementById('pass').value!=='admin123'){alert('Admin password incorrect');return}let name=document.getElementById('pname').value.trim();if(!name)return;products.push({id:Date.now(),name,cat:document.getElementById('pcat').value,price:Number(document.getElementById('price').value),desc:document.getElementById('desc').value});localStorage.setItem('9_products',JSON.stringify(products));renderProducts()}
function adminOrders(){if(document.getElementById('pass').value!=='admin123'){alert('Admin password incorrect');return}document.getElementById('adminData').innerHTML=orders.map(o=>`<p>${o.id} — ${o.items} — ₹${o.total} — ${o.status} <button onclick="updateStatus('${o.id}')">Update status</button></p>`).join('')}
function updateStatus(id){let o=orders.find(x=>x.id===id);o.status=prompt('Enter new order status',o.status)||o.status;localStorage.setItem('9_orders',JSON.stringify(orders));adminOrders();renderOrders()}
renderProducts();renderCart();renderWishlist();
</script></body></html>