<?php
// main-perfil.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo "<p>Você precisa estar logado para ver esta página.</p>";
    return;
}

require_once __DIR__ . '/db.php';

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT name, created_at, avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: /logout.php');
        exit();
    }

    $date = new DateTime($user['created_at']);
    $formattedJoinDate = "Entrou em " . $date->format('d/m/Y');
    $userAvatar = $user['avatar'] ?? 'https://ik.imagekit.io/kyjz2djk3p/avatar-15.png?updatedAt=1757344931522';

} catch (PDOException $e) {
    die("Erro ao buscar informações do perfil.");
}

// --- LÓGICA DO ROTEADOR DE CONTEÚDO ---
$view = $_GET['view'] ?? 'conta';
$allowed_views = ['conta', 'transacoes', 'historico', 'seguranca'];
if (!in_array($view, $allowed_views)) {
    $view = 'conta';
}
$content_file = __DIR__ . '/main-content-' . $view . '.php';

?>

<style>
/* ======================================= */
/* 1. Variáveis e Estilos Globais          */
/* ======================================= */
:root {
    --profile-bg: #1A1A1A;
    --sidebar-bg: #1F1F1F;
    --border-color: #27272a;
    --text-primary: #f0f0f0;
    --text-secondary: #a0a0a0;
    --primary-color: #28e504;
    --logout-color: #ff4d6a;
}

/* ==================================================== */
/* 2. Estilos Base (Mobile First - para telas < 768px) */
/* ==================================================== */

.profile-page-main {
    max-width: 1400px;
    margin: 1.5rem auto; /* Menos margem vertical no celular */
    padding: 0 1rem;     /* Menos espaçamento lateral */
    display: grid;
    /* Layout de coluna única por padrão */
    grid-template-columns: 1fr;
    gap: 1.5rem; /* Espaço entre a sidebar e o conteúdo quando empilhados */
    align-items: flex-start;
}

.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.user-info-card {
    background-color: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.25rem; /* Padding reduzido */
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-info-card .avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.user-info-card .details .name {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.1rem;
}

.user-info-card .details .join-date {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.profile-nav {
    background-color: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 0.75rem;
}

.profile-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
}

.profile-nav a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-radius: 8px;
    color: var(--text-secondary);
    font-weight: 500;
    text-decoration: none;
    transition: background-color 0.2s, color 0.2s;
}

.profile-nav a:hover {
    background-color: #2a2a2e;
    color: var(--text-primary);
}

.profile-nav a.is-active {
    background-color: var(--primary-color);
    color: #111;
    font-weight: 600;
}

.profile-nav a.logout-link { color: var(--logout-color); }
.profile-nav a.logout-link:hover { background-color: rgba(255, 77, 106, 0.1); }
.profile-nav a svg { width: 20px; height: 20px; }

.profile-content {
    background-color: var(--sidebar-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    min-height: 400px; /* Altura mínima menor para mobile */
    min-width: 0;
}

/* O conteúdo interno terá padding menor por padrão */
.profile-content > section {
    padding: 1.5rem;
}

/* ======================================================== */
/* 3. Ajustes para Telas Maiores (Tablets - 768px ou mais)  */
/* ======================================================== */
@media (min-width: 768px) {
    .profile-page-main {
        margin: 2rem auto;
        padding: 0 1.5rem;
        /* Layout de duas colunas para tablets */
        grid-template-columns: 260px 1fr;
        gap: 2rem;
    }

    .profile-content {
        min-height: 500px;
    }

    /* Aumenta o padding do conteúdo interno */
    .profile-content > section {
        padding: 2rem;
    }
}

/* ========================================================= */
/* 4. Ajustes para Telas Grandes (Desktop - 1200px ou mais) */
/* ========================================================= */
@media (min-width: 1200px) {
    .profile-page-main {
        /* Sidebar um pouco mais larga para desktops */
        grid-template-columns: 280px 1fr;
    }
}
</style>

<main class="profile-page-main">
    <aside class="profile-sidebar">
        <div class="user-info-card">
            <img src="<?= htmlspecialchars($userAvatar) ?>" alt="Avatar" class="avatar">
            <div class="details">
                <div class="name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="join-date"><?= $formattedJoinDate ?></div>
            </div>
        </div>

        <nav class="profile-nav">
            <ul>
                <li><a href="/perfil.php?view=conta" class="<?= $view === 'conta' ? 'is-active' : '' ?>"><svg fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg><span>Conta</span></a></li>
                <li><a href="/perfil.php?view=historico" class="<?= $view === 'historico' ? 'is-active' : '' ?>"><svg fill="currentColor" viewBox="0 0 24 24"><path d="m21.986 9.74-.008-.088A5.003 5.003 0 0 0 17 5H7a4.97 4.97 0 0 0-4.987 4.737q-.014.117-.013.253v6.51c0 .925.373 1.828 1.022 2.476A3.52 3.52 0 0 0 5.5 20c1.8 0 2.504-1 3.5-3 .146-.292.992-2 3-2 1.996 0 2.853 1.707 3 2 1.004 2 1.7 3 3.5 3 .925 0 1.828-.373 2.476-1.022A3.52 3.52 0 0 0 22 16.5V10q0-.141-.014-.26zM7 12.031a2 2 0 1 1-.001-3.999A2 2 0 0 1 7 12.031zm10-5a1 1 0 1 1 0 2 1 1 0 1 1 0-2zm-2 4a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2 2a1 1 0 1 1 0-2 1 1 0 1 1 0 2zm2-2a1 1 0 1 1 0-2 1 1 0 1 1 0 2z"/></svg><span>Histórico de Jogos</span></a></li>
                <li><a href="/perfil.php?view=transacoes" class="<?= $view === 'transacoes' ? 'is-active' : '' ?>"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M20 12v6a1 1 0 0 1-2 0V4a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v14c0 1.654 1.346 3 3 3h14c1.654 0 3-1.346 3-3v-6zm-6-1v2H6v-2zM6 9V7h8v2zm8 6v2h-3v-2z"></path></svg><span>Transações</span></a></li>
                <li><a href="/perfil.php?view=seguranca" class="<?= $view === 'seguranca' ? 'is-active' : '' ?>"><svg fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m0 5a1.5 1.5 0 0 1 .5 2.915l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99A1.5 1.5 0 0 1 8 5"></path></svg><span>Segurança</span></a></li>
                <li><a href="/logout.php" class="logout-link"><svg fill="currentColor" viewBox="0 0 32 32"><path d="M6 30h12a2 2 0 0 0 2-2v-3h-2v3H6V4h12v3h2V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v24a2 2 0 0 0 2 2Z"></path><path d="M20.586 20.586 24.172 17H10v-2h14.172l-3.586-3.586L22 10l6 6-6 6z"></path></svg><span>Sair</span></a></li>
            </ul>
        </nav>
    </aside>

    <section class="profile-content">
        <?php
        if (file_exists($content_file)) {
            require $content_file;
        } else {
            // Se o arquivo não existir, exibe uma mensagem padrão
            echo '<div style="padding: 2rem;"><p>Conteúdo em breve.</p></div>';
        }
        ?>
    </section>
</main>