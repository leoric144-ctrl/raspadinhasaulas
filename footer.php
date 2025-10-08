<?php
// footer.php

// Captura o nome do domínio
$dominio = $_SERVER['HTTP_HOST'] ?? 'raspadinhas.green';
?>
<style>
.footer{
    background:#111111;width:100%;font-size:.875rem;
}
.footer-container{display:flex;flex-wrap:wrap;max-width:1400px;margin:0 auto;padding:0 1rem;}
.footer-column-logo{width:35%;padding-right:1rem;}
.footer-column-links{width:65%;padding-left:3rem;}
.footer-logo{width:140px;margin-bottom:1rem;}
.footer-legal-text{font-size:.7rem;opacity:.65;margin-bottom:1rem;}
.footer-legal-text.secondary{opacity:.50;}
.footer-links-wrapper{display:flex;justify-content:flex-end;gap:8rem;}
.links-group strong{display:block;margin-bottom:.625rem;}
.links-group a{display:block;opacity:.7;margin-bottom:.75rem;transition:opacity .2s;}
.links-group a:hover{opacity:1;}
.footer-bottom-row{
    display:flex;justify-content:space-between;align-items:center;width:100%;
    margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border);
}
.footer-certifications{display:flex;align-items:center;gap:.5rem;}
.footer-certifications .cert-placeholder{
    background:#555;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:bold;border-radius:4px;
}
.footer-social-icons{display:flex;align-items:center;gap:.75rem;}
.footer-social-icons a{
    display:inline-flex;align-items:center;justify-content:center;background:rgba(0,0,0,.4);
    border-radius:9999px;padding:.625rem;transition:background-color .2s;
}
.footer-social-icons a:hover{background:rgba(0,0,0,.7);}
.footer-social-icons svg{width:1.25rem;height:1.25rem;}

/* Responsivo */
@media (max-width:992px){
    .footer-column-logo{width:40%;padding-right:.75rem;}
    .footer-column-links{width:60%;padding-left:2rem;}
    .footer-links-wrapper{gap:4rem;}
    .footer-bottom-row{flex-direction:column;align-items:flex-start;gap:1rem;}
}
@media (max-width:768px){
    .footer{padding:1.75rem 0;}
    .footer-container{padding:0 .75rem;}
    .footer-column-logo,.footer-column-links{width:100%;padding:0;}
    .footer-column-logo{margin-bottom:1.25rem;}
    .footer-links-wrapper{justify-content:flex-start;gap:3rem;flex-wrap:wrap;}
}
@media (max-width:480px){
    .footer{margin-top:1.5rem;padding:1.5rem 0 1.75rem;font-size:.8rem;}
    .footer-logo{width:120px;margin-bottom:.75rem;}
    .footer-legal-text{font-size:.65rem;margin-bottom:.75rem;}
    .footer-links-wrapper{flex-direction:column;gap:1.75rem;}
    .links-group strong{margin-bottom:.5rem;font-size:.9rem;}
    .links-group a{margin-bottom:.6rem;font-size:.8rem;}
    .footer-bottom-row{margin-top:1.5rem;padding-top:1.25rem;gap:1.25rem;}
    .footer-social-icons a{padding:.5rem;}
    .footer-social-icons svg{width:1.1rem;height:1.1rem;}
}
@media (max-height:600px){
    .footer{padding:1.5rem 0;}
}
</style>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-column-logo">
            <a href="/"><img src="https://ik.imagekit.io/3kbnnws8u/raspa-green-logo.png?updatedAt=1757348357863" class="footer-logo" alt="Logo"></a>
            <div class="footer-legal-text">© 2025 <?= htmlspecialchars($dominio) ?> Todos os direitos reservados.</div>
            <div class="footer-legal-text secondary">
                Raspadinhas e outros jogos de azar são regulamentados e cobertos pela nossa licença de jogos. Jogue com responsabilidade.
            </div>
        </div>

        <div class="footer-column-links">
            <div class="footer-links-wrapper">
                <div class="links-group">
                    <strong>Regulamentos</strong>
                    <a href="/jogo-responsavel.php">Jogo responsável</a>
                    <a href="/politica-de-privacidade.php">Política de Privacidade</a>
                    <a href="/termos.php">Termos de Uso</a>
                </div>
                <div class="links-group">
                    <strong>Ajuda</strong>
                    <a href="/perguntas-frequentes.php">Perguntas Frequentes</a>
                    <a href="/comojogar.php">Como Jogar</a>
                    <a href="/suporte-tecnico.php">Suporte Técnico</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom-row">
            <div class="footer-certifications">
                <div class="cert-placeholder" style="width:4rem;height:4rem;">GCB</div>
                <div class="cert-placeholder" style="width:2.25rem;height:2.25rem;">18+</div>
            </div>
            <div class="footer-social-icons">
                <a href="#" target="_blank">
                    <svg fill="currentColor" viewBox="0 0 16 16"><path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.942a3.916 3.916 0 0 0-.923-1.417A3.916 3.916 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.389.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.843-.038 1.096-.047 3.232-.047h.001zM8 4.865a3.135 3.135 0 1 0 0 6.27 3.135 3.135 0 0 0 0-6.27zM8 12A4 4 0 1 1 8 4a4 4 0 0 1 0 8zm6.406-7.979a1.2 1.2 0 1 0 0-2.4 1.2 1.2 0 0 0 0 2.4z"/></svg>
                </a>
                <a href="#" target="_blank">
                    <svg fill="currentColor" viewBox="0 0 16 16"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/></svg>
                </a>
                <a href="#" target="_blank">
                    <svg fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.577.298-.595.442-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294.26.006.549-.1.868-.32 2.179-1.471 3.304-2.214 3.374-2.23.05-.012.12-.026.166.016.047.041.042.12.037.141-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8.154 8.154 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629.093.06.183.125.27.187.331.226.615.42.857.536.266.126.501.24.697.24.252 0 .49-.117.65-.26.139-.125.25-.306.323-.478.067-.152.076-.323.076-.377l-.005-.242a.51.51 0 0 0-.017-.171l-.005-.002c-.001-.003-.002-.007-.004-.01l-.017-.034c-.038-.083-.088-.168-.138-.243-.247-.375-1.08-1.147-1.548-1.582-.468-.435-.909-.838-1.314-1.214-.24-.225-.504-.447-.753-.647-.26-.213-.5-403-.663-.56-.123.14-.3.28-.465.432-.3.27-.6.533-.9.774-.247.203-.495.405-.737.614-.248.208-.49.418-.718.633-.222.21-.43.41-.637.599-.2.187-.384.358-.55.518-.129.124-.25.24-.356.343-.377.37-.8.746-1.127 1.021l-.017.015-.005.005c-.003.003-.005.005-.007.007l-.01.009c-.004.003-.009.006-.013.009-.018.01-.04.02-.06.026-.016.004-.034.006-.05.006a.097.097 0 0 1-.057-.015z"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
// Inicialização dos Swipers após tudo carregado
document.addEventListener('DOMContentLoaded', function(){
    const heroSwiper = new Swiper('.hero-swiper', {
        loop:true,
        autoplay:{delay:5000,disableOnInteraction:true},
        pagination:{el:'.swiper-pagination',clickable:false},
        navigation:{nextEl:'.hero-next-button',prevEl:'.hero-prev-button'},
    });

    const winnersSwiper = new Swiper('.winners-swiper', {
        loop:true,
        slidesPerView:'auto',
        spaceBetween:16,
        freeMode:true,
        autoplay:{delay:1000,disableOnInteraction:true},
    });

    // Toggle texto "Mostrar/Ocultar" senha dos modais
    document.querySelectorAll('.password-toggle').forEach(toggle=>{
        toggle.addEventListener('click', ()=>{
            const input = toggle.previousElementSibling;
            if(input.type === 'password'){ input.type='text'; toggle.textContent='Ocultar'; }
            else{ input.type='password'; toggle.textContent='Mostrar'; }
        });
    });
});
console.log('Footer JS carregado');
</script>

</body>
</html>