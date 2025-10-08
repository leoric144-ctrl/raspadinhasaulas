<?php // destaques.php ?>
<style>
/* Destaques & Grid */
.highlights-header{
    display:flex;justify-content:space-between;align-items:center;
}
.highlights-header .title{
    display:flex;align-items:center;gap:.5rem;font-size:1.25rem;font-weight:500;
}
.highlights-header .title svg{color:#f59e0b;}
.highlights-header .view-more{
    display:flex;align-items:center;gap:.5rem;font-size:.875rem;font-weight:500;transition:color .2s;
}
.highlights-header .view-more:hover{color:var(--primary);}
.scratch-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
.scratch-card{
    display:flex;flex-direction:column;gap:1rem;border:1px solid var(--border);border-radius:.75rem;padding:1rem;
    background-image:linear-gradient(to top, rgba(40,229,4,.06), transparent 45%);border-bottom-width:2px;transition:border-color .3s;
}
.scratch-card:hover{border-bottom-color:var(--primary);}
.scratch-card .card-image{width:100%;aspect-ratio:5/1;overflow:hidden;border-radius:4px;}
.scratch-card .card-image img{width:100%;height:100%;object-fit:cover;}
.scratch-card .card-header{display:flex;flex-direction:column;align-items:flex-start;gap:.25rem;}
.scratch-card .card-header .title{font-weight:600;font-size:1.1rem;}
.scratch-card .card-header .prize-info{font-size:.75rem;color:#f59e0b;font-weight:500;text-transform:uppercase;}
.scratch-card .card-footer{display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
/* Botão jogar */
.scratch-card .play-button{
    display:inline-flex;align-items:center;justify-content:center;gap:.5rem;background:var(--primary);color:#000;font-weight:600;
    padding:.6rem 1rem;border-radius:.5rem;font-size:1rem;
}
.scratch-card .play-button .price{
    background:#1A1A1A;color:#fff;padding:.4rem .6rem;border-radius:.375rem;font-size:.875rem;display:inline-flex;align-items:center;
}
.scratch-card .play-button .price-currency{color:#2dd4bf;font-weight:bold;}
.scratch-card .view-prizes{
    display:flex;align-items:center;gap:.25rem;font-size:.8rem;font-weight:600;text-transform:uppercase;transition:color .2s;
}
.scratch-card .view-prizes:hover{color:var(--primary);}

/* RESPONSIVO */
@media (max-width:992px){
    .highlights-header{margin-top:2rem;margin-bottom:1.25rem;}
    .scratch-grid{grid-template-columns:repeat(2,1fr);gap:.9rem;}
    .scratch-card{padding:.9rem;}
    .scratch-card .card-image{aspect-ratio:30/6;}
}

@media (max-width:480px){
    .highlights-header{
        margin-top:1.75rem;margin-bottom:1rem;
        flex-direction:column;align-items:flex-start;gap:.5rem;
    }
    .highlights-header .title{font-size:1.05rem;}
    .highlights-header .view-more{font-size:.8rem;}

    .scratch-grid{grid-template-columns:1fr;gap:.85rem;}
    .scratch-card{gap:.85rem;border-radius:.6rem;padding:.85rem .8rem;}
    .scratch-card .card-header .title{font-size:1rem;}

    .scratch-card .card-footer{
        display:flex;
        flex-wrap:nowrap;
        align-items:center;
        justify-content:space-between;
        gap:.6rem;
        flex-direction:row;
    }
    .scratch-card .play-button{
        font-size:.9rem;padding:.55rem .75rem;border-radius:.45rem;
        flex:0 0 auto;
    }
    .scratch-card .play-button .price{font-size:.8rem;padding:.3rem .5rem;}
    .scratch-card .view-prizes{
        font-size:.75rem;
        line-height:1;
        margin-left:auto;
        flex:0 0 auto;
    }
}

@media (max-height:600px){
    .scratch-grid{gap:.75rem;}
}

</style>

<section class="highlights-header">
    <h1 class="title">
        <svg width="1em" height="1em" fill="currentColor" class="bi bi-fire text-amber-400 animate-pulse duration-700" viewBox="0 0 16 16"><path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/></svg>
        Destaques
    </h1>
    <a href="/raspadinhas.php" class="view-more">
        <span>Ver mais</span>
        <svg width="1em" height="1em" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 4 8 8-8 8"/></svg>
    </a>
</section>

<div class="scratch-grid">
<?php
// ✅ CHANGE (PHP): Added a 'url_view' key to each card
$scratch_cards = [
    ['img'=>'https://ik.imagekit.io/3kbnnws8u/troco%20premiado.png?updatedAt=1757350104920','alt'=>'Troco Premiado','title'=>'Troco Premiado','prize_info'=>'PRÊMIOS DE ATÉ R$ 1.000,00','price'=>'0,50', 'url_view' => 'centavo-da-sorte'],

    ['img'=>'https://ik.imagekit.io/3kbnnws8u/Tech%20Mania.png?updatedAt=1757350104698','alt'=>'Tech Mania','title'=>'Tech Mania','prize_info'=>'PRÊMIOS DE ATÉ R$ 2.500,00','price'=>'1,00', 'url_view' => 'sorte-instantanea'],

    ['img'=>'https://ik.imagekit.io/3kbnnws8u/apple%20mania.png?updatedAt=1757350104484','alt'=>'Apple Mania','title'=>'Apple Mania','prize_info'=>'PRÊMIOS DE ATÉ R$ 5.000,00','price'=>'2,50', 'url_view' => 'raspadinha-suprema'],

    ['img'=>'https://ik.imagekit.io/3kbnnws8u/beleza%20premiada.png?updatedAt=1757350104732','alt'=>'Beleza Premiada','title'=>'Beleza Premiada','prize_info'=>'PRÊMIOS DE ATÉ R$ 15.000,00','price'=>'5,00', 'url_view' => 'raspa-relampago'],

    ['img'=>'https://ik.imagekit.io/3kbnnws8u/Luxo%20Rasp%C3%A1vel.png?updatedAt=1757350104504','alt'=>'Luxo Raspável','title'=>'Luxo Raspável','prize_info'=>'PRÊMIOS DE ATÉ R$ 30.000,00','price'=>'50,00', 'url_view' => 'raspadinha-magica'],

    ['img'=>'https://ik.imagekit.io/3kbnnws8u/Casa%20dos%20Sonhos.png?updatedAt=1757350104532','alt'=>'Casa dos Sonhos','title'=>'Casa dos sonhos','prize_info'=>'PRÊMIOS DE ATÉ R$ 60.000,00','price'=>'100,00', 'url_view' => 'raspe-e-ganhe'],
];
foreach($scratch_cards as $card){ ?>
    <div class="scratch-card">
        <div class="card-image"><img src="<?= htmlspecialchars($card['img']) ?>" alt="<?= htmlspecialchars($card['alt']) ?>"></div>
        <div class="card-header">
            <h1 class="title"><?= htmlspecialchars($card['title']) ?></h1>
            <h2 class="prize-info"><?= htmlspecialchars($card['prize_info']) ?></h2>
        </div>
        <div class="card-footer">
            <a href="?view=<?= htmlspecialchars($card['url_view']) ?>" class="play-button">
                <svg fill="currentColor" viewBox="0 0 256 256" width="1em" height="1em"><path d="M198.51 56.09C186.44 35.4 169.92 24 152 24h-48c-17.92 0-34.44 11.4-46.51 32.09C46.21 75.42 40 101 40 128s6.21 52.58 17.49 71.91C69.56 220.6 86.08 232 104 232h48c17.92 0 34.44-11.4 46.51-32.09C209.79 180.58 216 155 216 128s-6.21-52.58-17.49-71.91Zm1.28 63.91h-32a152.8 152.8 0 0 0-9.68-48h30.59c6.12 13.38 10.16 30 11.09 48Zm-20.6-64h-28.73a83 83 0 0 0-12-16H152c10 0 19.4 6 27.19 16ZM152 216h-13.51a83 83 0 0 0 12-16h28.73C171.4 210 162 216 152 216Zm36.7-32h-30.58a152.8 152.8 0 0 0 9.68-48h32c-.94 18-4.98 34.62-11.1 48Z"/></svg>
                <span>Jogar</span>
                <div class="price"><span class="price-currency">R$</span> <?= htmlspecialchars($card['price']) ?></div>
            </a>
            <a href="?view=<?= htmlspecialchars($card['url_view']) ?>" class="view-prizes"> <svg viewBox="0 0 512 512" fill="currentColor" width="1rem" height="1rem"><path d="m190.5 68.8 34.8 59.2H152c-22.1 0-40-17.9-40-40s17.9-40 40-40h2.2c14.9 0 28.8 7.9 36.3 20.8zM64 88c0 14.4 3.5 28 9.6 40H32c-17.7 0-32 14.3-32 32v64c0 17.7 14.3 32 32 32h448c17.7 0 32-14.3 32-32v-64c0-17.7-14.3-32-32-32h-41.6c6.1-12 9.6-25.6 9.6-40 0-48.6-39.4-88-88-88h-2.2c-31.9 0-61.5 16.9-77.7 44.4L256 85.5l-24.1-41C215.7 16.9 186.1 0 154.2 0H152c-48.6 0-88 39.4-88 88zm336 0c0 22.1-17.9 40-40 40h-73.3l34.8-59.2c7.6-12.9 21.4-20.8 36.3-20.8h2.2c22.1 0 40 17.9 40 40zM32 288v176c0 26.5 21.5 48 48 48h144V288zm256 224h144c26.5 0 48-21.5 48-48V288H288z"/></svg>
                <span> VER PRÊMIOS </span>
            </a>
        </div>
    </div>
<?php } ?>
</div>