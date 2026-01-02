<?php
session_start();
require_once 'config.php';
require_once 'db_engine.php';

/* ------------------------------
   USER CONTEXT
------------------------------ */
$user_id   = $_SESSION['user_id'] ?? null;
$vault     = $user_id ? DBEngine::readJSON("user_data/$user_id.json") : [];
$history   = $vault['history'] ?? [];
$following = $vault['following'] ?? [];

/* ------------------------------
   LOAD POSTS + ALGO
------------------------------ */
$posts = DBEngine::readJSON("posts.json") ?? [];
$all_tags = [];

foreach ($posts as &$post) {
    if (!empty($post['tags'])) {
        foreach ((array)$post['tags'] as $t) {
            $all_tags[] = trim($t);
        }
    }

    $score = 0;
    if (in_array($post['author_id'] ?? '', $following)) $score += 50;
    if (in_array($post['post_id'], $history)) $score -= 100;
    $score += min(floor(($post['views'] ?? 0) / 10), 30);

    $post['algo_score'] = $score * (rand(80,120)/100);
}

usort($posts, fn($a,$b)=>$b['algo_score'] <=> $a['algo_score']);
$unique_tags = array_slice(array_unique($all_tags),0,12);
$initial_posts = array_slice($posts,0,6);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Epistora | Discover Stories</title>
<link rel="icon" href="/assets/logo.png">

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#2563eb'
      }
    }
  }
}
</script>

</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">

<!-- ================= HEADER ================= -->
<header class="sticky top-0 z-50 bg-white dark:bg-slate-900 shadow-md">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

    <!-- LOGO -->
    <a href="index.php" class="flex items-center gap-2 font-extrabold text-2xl text-primary hover:text-blue-700 transition">
      <img src="/assets/logo.png" alt="Epistora" class="w-8 h-8">
      EPISTORA
    </a>

    <!-- DESKTOP SEARCH -->
    <div class="hidden md:flex flex-1 mx-6 relative">
      <input id="main-search"
        type="text"
        placeholder="Search stories, writers, tags…"
        class="w-full rounded-full border dark:border-slate-700 bg-slate-100 dark:bg-slate-800 px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none transition">
      <div id="search-results-dropdown" class="hidden absolute top-12 w-full bg-white dark:bg-slate-800 shadow-xl rounded-xl overflow-hidden z-50"></div>
    </div>

    <!-- ACTIONS -->
    <div class="flex items-center gap-4">

      <!-- DARK MODE TOGGLE -->
      <button id="themeToggle"
        class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition">
        <span id="themeIcon">🌙</span>
      </button>

      <!-- NAVIGATION (DESKTOP) -->
      <nav class="hidden md:flex items-center gap-4 text-sm font-medium">
        <?php if(isset($_SESSION['user_id'])): ?>
          <?php if($_SESSION['role']==='user'): ?>
            <a href="/user/apply_writer.php"
               class="px-4 py-2 rounded-full bg-primary text-white hover:bg-blue-700 transition">Become a Writer</a>
          <?php else: ?>
            <a href="post/create/"
               class="px-4 py-2 rounded-full bg-primary text-white hover:bg-blue-700 transition">Write</a>
          <?php endif; ?>

          <a href="user/dashboard/" class="hover:text-primary transition">Dashboard</a>
          <a href="user/logout.php" class="px-3 py-1 rounded-full bg-red-500 text-white hover:bg-red-600 transition">Logout</a>
        <?php else: ?>
          <a href="user/login/" class="px-4 py-2 rounded-full bg-primary text-white hover:bg-blue-700 transition">Sign In</a>
        <?php endif; ?>
      </nav>

      <!-- MOBILE MENU BUTTON -->
      <button id="menuBtn" class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- MOBILE NAVIGATION -->
  <nav id="navMenu" class="md:hidden hidden bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700">
    <div class="flex flex-col p-4 gap-3">
      <?php if(isset($_SESSION['user_id'])): ?>
        <?php if($_SESSION['role']==='user'): ?>
          <a href="/user/apply_writer/index.php" class="px-4 py-2 rounded-full bg-primary text-white hover:bg-blue-700 transition">Become a Writer</a>
        <?php else: ?>
          <a href="post/create/" class="px-4 py-2 rounded-full bg-primary text-white hover:bg-blue-700 transition">Write</a>
        <?php endif; ?>
        <a href="user/dashboard/" class="hover:text-primary transition">Dashboard</a>
        <a href="user/logout.php" class="px-3 py-1 rounded-full bg-red-500 text-white hover:bg-red-600 transition">Logout</a>
      <?php else: ?>
        <a href="user/login/" class="px-4 py-2 rounded-full bg-primary text-white hover:bg-blue-700 transition">Sign In</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<!-- ================= TAG RIBBON ================= -->
<div class="bg-white dark:bg-slate-900 border-b dark:border-slate-700 overflow-x-auto">
  <div class="max-w-7xl mx-auto px-4 py-3 whitespace-nowrap">
    <span class="text-xs font-semibold text-slate-500 mr-2">TOPICS:</span>
    <?php foreach($unique_tags as $tag): ?>
      <a href="search.php?q=<?=urlencode($tag)?>"
         class="inline-block mr-2 px-3 py-1 text-xs rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-primary hover:text-white transition">
        #<?=htmlspecialchars($tag)?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ================= MAIN FEED ================= -->
<main class="max-w-7xl mx-auto px-4 py-10">

<!-- Feed -->
<div id="feed" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
<?php foreach($initial_posts as $post): ?>
  <article class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow hover:-translate-y-1 transition">
    <span class="text-xs font-bold text-primary uppercase">
      <?=htmlspecialchars($post['tags'][0] ?? 'General')?>
    </span>

    <h2 class="mt-2 text-lg font-semibold">
      <a href="post/view/?id=<?=$post['post_id']?>" class="hover:text-primary">
        <?=htmlspecialchars($post['title'])?>
      </a>
    </h2>

    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
      <?=htmlspecialchars($post['preview'] ?? '')?>
    </p>

    <div class="mt-4 flex justify-between text-xs text-slate-500">
      <span><?=htmlspecialchars($post['author'])?></span>
      <span>👁 <?=$post['views'] ?? 0?></span>
    </div>
  </article>
<?php endforeach; ?>
</div>

<!-- Skeletons -->
<div id="skeletons"
     class="hidden mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
<?php for($i=0;$i<6;$i++): ?>
  <div class="animate-pulse bg-white dark:bg-slate-800 p-6 rounded-2xl shadow space-y-4">
    <div class="h-3 w-20 bg-slate-200 dark:bg-slate-700 rounded"></div>
    <div class="h-5 w-full bg-slate-200 dark:bg-slate-700 rounded"></div>
    <div class="h-4 w-5/6 bg-slate-200 dark:bg-slate-700 rounded"></div>
    <div class="flex justify-between pt-4">
      <div class="h-3 w-24 bg-slate-200 dark:bg-slate-700 rounded"></div>
      <div class="h-3 w-12 bg-slate-200 dark:bg-slate-700 rounded"></div>
    </div>
  </div>
<?php endfor; ?>
</div>

</main>

<!-- ================= FOOTER ================= -->
<footer class="text-center py-10 text-sm text-slate-500">
  © 2025 Epistora — JSON Flat-File Engine
</footer>

<!-- ================= JS ================= -->
<script>
/* DARK MODE AUTO */
const root = document.documentElement;
const savedTheme = localStorage.theme;
if(savedTheme) root.classList.toggle('dark', savedTheme==='dark');
else root.classList.toggle('dark', matchMedia('(prefers-color-scheme: dark)').matches);

document.getElementById('themeToggle').onclick = () => {
  root.classList.toggle('dark');
  localStorage.theme = root.classList.contains('dark') ? 'dark' : 'light';
};

/* MOBILE MENU */
document.getElementById('menuBtn').onclick = () =>
  document.getElementById('navMenu').classList.toggle('hidden');

/* INFINITE SCROLL */
let page = 1, loading = false;
const feed = document.getElementById('feed');
const skeletons = document.getElementById('skeletons');

async function loadMore(){
  if(loading) return;
  loading = true;
  skeletons.classList.remove('hidden');
  page++;

  const res = await fetch(`actions/load_posts.php?page=${page}`);
  const html = await res.text();

  if(html.trim()) feed.insertAdjacentHTML('beforeend', html);
  skeletons.classList.add('hidden');
  loading = false;
}

window.addEventListener('scroll', ()=>{
  if(window.innerHeight + scrollY >= document.body.offsetHeight - 500){
    loadMore();
  }
});
</script>

</body>
</html>
