<?php

// Array com os dados dos prêmios
$prizes = [
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_jbl_boombox_3_black.png?updatedAt=1757352247599', 'name' => 'Caixa de som JBL', 'value' => '2.500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_iphone_12.png?updatedAt=1757352244936', 'name' => 'iPhone 12', 'value' => '2.500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png', 'name' => '1.000 Reais', 'value' => '1.000,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_c2_nk109.png?updatedAt=1757352240875', 'name' => 'Smartphone', 'value' => '800,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png', 'name' => '700 Reais', 'value' => '700,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_ft_5_branca_e_preta.png?updatedAt=1757352243992', 'name' => 'Bola de futebol', 'value' => '500,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_212_vip_black.png?updatedAt=1757352240894', 'name' => 'Perfume 212', 'value' => '399,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_camisa_do_seu_time.png?updatedAt=1757352240917', 'name' => 'Camisa de time', 'value' => '350,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_fone_de_ouvido_lenovo.png?updatedAt=1757352243935', 'name' => 'Fone de ouvido', 'value' => '220,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/200-REAIS.png', 'name' => '200 Reais', 'value' => '200,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_copo_t_rmico_stanley_preto.png?updatedAt=1757352242839', 'name' => 'Copo Stanley', 'value' => '165,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png', 'name' => '100 Reais', 'value' => '100,00'],
    ['img' => 'https://ik.imagekit.io/kyjz2djk3p/powerbank.png', 'name' => 'PowerBank', 'value' => '60,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png', 'name' => '50 Reais', 'value' => '50,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/item_chinelo_havaianas_top_branco.png?updatedAt=1757352242753', 'name' => 'Chinelo Havaianas', 'value' => '35,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/10-reais.png', 'name' => '10 Reais', 'value' => '10,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/5-reais.png', 'name' => '5 Reais', 'value' => '5,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/3%20reais.png', 'name' => '3 Reais', 'value' => '3,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/2-reais.png', 'name' => '2 Reais', 'value' => '2,00'],
    ['img' => 'https://ik.imagekit.io/3kbnnws8u/PRIZES/1-real.png', 'name' => '1 Real', 'value' => '1,00'],
];
?>


<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

<style>
    /* --- SEÇÃO DE PRÊMIOS --- */
    .prizes-section {
        padding-top: 1rem;
    }

    .prizes-title {
        color: #f0f0f0;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* --- Container do Carrossel --- */
    .prizes-layout-container {
        width: 100%;
        overflow: hidden;
    }

    /* --- CARD DE PRÊMIO --- */
    .prize-card {
        background-color: #2A2A2E;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s ease-in-out;
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .prize-card:hover {
        transform: translateY(-5px);
    }

    .prize-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 50%;
        background: linear-gradient(to top, rgba(40, 229, 4, 0.20) 0%, transparent 80%);
        pointer-events: none;
        z-index: 1;
    }

    .prize-card > * {
        position: relative;
        z-index: 2;
    }

    .prize-card .prize-image {
        width: 100%;
        max-width: 100px;
        height: 100px;
        object-fit: contain;
    }

    .prize-card .prize-name {
        color: #E5E5E5;
        font-size: 0.9rem;
        font-weight: 500;
        min-height: 2.5em;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .prize-card .prize-value {
        background-color: #fff;
        color: #0e0b0c;
        font-weight: 510;
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        text-align: left;
        box-sizing: border-box;
    }

    /* --- Estilos do Carrossel para todas as telas --- */
    .prizes-layout-container {
        padding-bottom: 2rem;
    }
    .prizes-layout-container .swiper-slide {
        width: 160px; /* Largura fixa para cada card */
        flex-shrink: 0;
    }
    .prizes-layout-container .swiper-pagination-bullet {
        background: #fff;
        opacity: 0.5;
    }
    .prizes-layout-container .swiper-pagination-bullet-active {
        background: #28e504;
        opacity: 1;
    }

</style>

<section class="prizes-section">
    <h2 class="prizes-title">Prêmios da Raspadinha:</h2>

    <div class="swiper prizes-layout-container">
        <div class="swiper-wrapper">
            <?php foreach ($prizes as $prize): ?>
                <div class="swiper-slide">
                    <div class="prize-card">
                        <img src="<?php echo htmlspecialchars($prize['img']); ?>" alt="<?php echo htmlspecialchars($prize['name']); ?>" class="prize-image">
                        <p class="prize-name"><?php echo htmlspecialchars($prize['name']); ?></p>
                        <div class="prize-value">
                            R$ <?php echo htmlspecialchars($prize['value']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.prizes-layout-container', {
        slidesPerView: 'auto',
        spaceBetween: 16,
        freeMode: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
    });
});
</script>