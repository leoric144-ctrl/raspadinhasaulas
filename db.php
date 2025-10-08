<?php
// db.php - Coração do projeto: Conecta ao banco e garante que o schema completo exista.
// Funciona localmente (variáveis .env) e no Heroku (DATABASE_URL).

if (!function_exists('db')) {
    /**
     * Obtém a conexão PDO com o banco de dados.
     * Na primeira conexão a um banco de dados vazio, também cria toda a estrutura de tabelas,
     * relações e insere dados iniciais essenciais.
     *
     * @return PDO A instância da conexão PDO.
     */
    function db(): PDO
    {
        static $pdo = null;
        if ($pdo) {
            return $pdo;
        }

        // Lógica de conexão para Heroku ou ambiente local
        $dbUrl = getenv('DATABASE_URL');
        if ($dbUrl) {
            $parts = parse_url($dbUrl);
            $host = $parts['host'];
            $user = $parts['user'];
            $pass = $parts['pass'];
            $port = $parts['port'] ?? '5432'; // Usa a porta padrão 5432 se a URL não especificar
            $dbname = ltrim($parts['path'], '/');
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        } else {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'raspagreen';
            $user = getenv('DB_USER') ?: 'postgres';
            $pass = getenv('DB_PASS') ?: 'postgres';
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        }

        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $opts);

            // Garante que o schema completo do banco de dados está inicializado.
            _initializeDatabaseSchema($pdo);

        } catch (Exception $e) {
            http_response_code(500);
            $errorMessage = getenv('APP_ENV') === 'development' ? $e->getMessage() : 'Erro crítico no servidor.';
            echo json_encode(['ok' => false, 'error' => $errorMessage]);
            exit;
        }

        return $pdo;
    }
}

/**
 * [Função Interna] Verifica se o schema do DB já foi criado e, se não, cria-o.
 * Usa uma tabela marcadora para evitar checagens custosas em toda requisição.
 *
 * @param PDO $pdo A instância da conexão PDO.
 * @throws Exception Se a inicialização falhar.
 */
function _initializeDatabaseSchema(PDO $pdo): void
{
    $markerTable = '__schema_initialized_v2'; // MUDANÇA: Atualizado para a versão 2 para recriar o schema

    $stmt = $pdo->query("SELECT to_regclass('public.{$markerTable}')");
    if ($stmt->fetchColumn()) {
        return; // Schema já existe, operação concluída.
    }

    try {
        $pdo->beginTransaction();

        // Limpa o banco de dados para garantir um estado limpo para o novo schema
        $pdo->exec("
            DROP TABLE IF EXISTS referral_levels, users, withdrawals, commission_transactions, deposits, historicplay, referral_deposits, transactions, payment_methods, games, bonus_system CASCADE;
            DROP FUNCTION IF EXISTS set_updated_at CASCADE;
            DROP TABLE IF EXISTS __schema_initialized_v1 CASCADE;
        ");

        // 1. FUNÇÃO PARA TRIGGER
        $pdo->exec("CREATE OR REPLACE FUNCTION set_updated_at() RETURNS TRIGGER AS $$ BEGIN NEW.updated_at = NOW(); RETURN NEW; END; $$ LANGUAGE plpgsql;");

        // 2. CRIAÇÃO DAS TABELAS
        $pdo->exec("CREATE TABLE IF NOT EXISTS referral_levels (id SERIAL PRIMARY KEY, name VARCHAR(50) NOT NULL UNIQUE, min_active_indications INTEGER NOT NULL DEFAULT 0, commission_rate NUMERIC(5, 3) NOT NULL, level_image_url TEXT, min_xp INTEGER NOT NULL DEFAULT 0);");
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY, name VARCHAR(120) NOT NULL, email VARCHAR(180) NOT NULL UNIQUE, phone VARCHAR(30) UNIQUE, password_hash TEXT NOT NULL, created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(), avatar TEXT, saldo NUMERIC(12, 2) NOT NULL DEFAULT 0.00, last_login TIMESTAMP WITHOUT TIME ZONE, updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(), total_deposited NUMERIC(12, 2) NOT NULL DEFAULT 0.00, total_withdrawn NUMERIC(12, 2) NOT NULL DEFAULT 0.00, cashback_earnings NUMERIC(12, 2) NOT NULL DEFAULT 0.00, username VARCHAR(50) UNIQUE, document VARCHAR(20), referrer_id INTEGER, commission_balance NUMERIC(12, 2) NOT NULL DEFAULT 0.00, referral_code VARCHAR(10) UNIQUE, total_commission_earned NUMERIC(12, 2) NOT NULL DEFAULT 0.00, total_commission_withdrawn NUMERIC(12, 2) NOT NULL DEFAULT 0.00, commission_rate NUMERIC(5, 3) NOT NULL DEFAULT 0.050, level_id INTEGER NOT NULL DEFAULT 1, xp INTEGER NOT NULL DEFAULT 0, is_blocked BOOLEAN DEFAULT FALSE, rollover_amount NUMERIC(12, 2) NOT NULL DEFAULT 0.00);");
        $pdo->exec("CREATE TABLE IF NOT EXISTS withdrawals (id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL, amount NUMERIC(10, 2) NOT NULL, pix_key_type VARCHAR(50) NOT NULL, pix_key VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'PENDING', created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, processed_at TIMESTAMP WITHOUT TIME ZONE, rejection_reason TEXT, withdrawal_ref_id VARCHAR(255) UNIQUE);");
        $pdo->exec("CREATE TABLE IF NOT EXISTS commission_transactions (id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL, referred_user_id INTEGER, type VARCHAR(50) NOT NULL, amount NUMERIC(12, 2) NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'completed', description TEXT, created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(), updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW());");
        $pdo->exec("CREATE TABLE IF NOT EXISTS deposits (id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL, amount NUMERIC(12, 2) NOT NULL, status VARCHAR(50) NOT NULL DEFAULT 'pending', payment_method VARCHAR(100), transaction_id VARCHAR(255), created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(), updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW());");
        $pdo->exec("CREATE TABLE IF NOT EXISTS historicplay (id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL, game_name VARCHAR(100) NOT NULL, bet_amount NUMERIC(12, 2) NOT NULL, action VARCHAR(20) NOT NULL, prize_amount NUMERIC(12, 2) DEFAULT 0.00, round_id VARCHAR(255) UNIQUE, played_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP);");
        $pdo->exec("CREATE TABLE IF NOT EXISTS referral_deposits (id SERIAL PRIMARY KEY, referrer_user_id INTEGER NOT NULL, referred_user_id INTEGER NOT NULL, deposit_id INTEGER NOT NULL, commission_amount NUMERIC(12, 2) NOT NULL, commission_rate NUMERIC(5, 3) NOT NULL, created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW());");
        $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (id SERIAL PRIMARY KEY, user_id INTEGER NOT NULL, amount NUMERIC(12, 2) NOT NULL, status VARCHAR(20) NOT NULL, provider VARCHAR(50), provider_transaction_id VARCHAR(255) UNIQUE, created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(), updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT NOW(), pix_code TEXT, withdrawal_id INTEGER, description TEXT, type VARCHAR(50), provider_hash VARCHAR(255));");
        $pdo->exec("CREATE TABLE IF NOT EXISTS payment_methods (id SERIAL PRIMARY KEY, name VARCHAR(100) NOT NULL, provider_key VARCHAR(50) NOT NULL UNIQUE, is_active BOOLEAN NOT NULL DEFAULT TRUE, icon_url TEXT, description TEXT);");

        // ✅ NOVO: Tabela para gerenciar os jogos (games)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS games (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                bet_cost NUMERIC(10, 2) NOT NULL,
                prizes_json JSONB NOT NULL,
                win_chance_percent NUMERIC(5, 2) NOT NULL DEFAULT 0.00
            );
        ");

        // ✅ NOVO: Tabela para gerenciar o sistema de bônus
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS bonus_system (
                id SERIAL PRIMARY KEY,
                game_name VARCHAR(100) NOT NULL UNIQUE,
                faturamento_meta NUMERIC(12, 2) NOT NULL,
                bonus_amount NUMERIC(12, 2) NOT NULL,
                current_faturamento NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
                current_bonus_paid NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
                is_bonus_active BOOLEAN NOT NULL DEFAULT TRUE,
                CONSTRAINT fk_bonus_system_game_name FOREIGN KEY (game_name) REFERENCES games(name) ON DELETE CASCADE
            );
        ");

        // 3. CRIAÇÃO E ATUALIZAÇÃO DE COLUNAS/ÍNDICES
        _add_column_if_not_exists($pdo, 'users', 'is_level_manual_override', 'BOOLEAN NOT NULL DEFAULT FALSE');
        _add_column_if_not_exists($pdo, 'users', 'is_demo', 'BOOLEAN NOT NULL DEFAULT FALSE');
        _add_column_if_not_exists($pdo, 'users', 'demo_win_rate', 'NUMERIC(5, 2) NULL DEFAULT NULL');
        _add_column_if_not_exists($pdo, 'users', 'is_blocked', 'BOOLEAN DEFAULT FALSE');
        _add_column_if_not_exists($pdo, 'users', 'rollover_amount', 'NUMERIC(12, 2) NOT NULL DEFAULT 0.00');
        _add_column_if_not_exists($pdo, 'transactions', 'type', 'VARCHAR(50)');
        _add_column_if_not_exists($pdo, 'transactions', 'provider_hash', 'VARCHAR(255)');
        _add_unique_constraint_if_not_exists($pdo, 'historicplay', 'historicplay_round_id_unique', 'round_id');

        // 4. CRIAÇÃO DOS ÍNDICES
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_referral_levels_min_active_indications ON referral_levels(min_active_indications);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_referrer_id ON users(referrer_id);");
        $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_referral_code_unique ON users(referral_code) WHERE referral_code IS NOT NULL;");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_withdrawals_user_id ON withdrawals(user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_commission_transactions_user_id ON commission_transactions(user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_deposits_user_id ON deposits(user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_deposits_status ON deposits(status);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_referral_deposits_referrer_user_id ON referral_deposits(referrer_user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_referral_deposits_referred_user_id ON referral_deposits(referred_user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_transactions_user_id ON transactions(user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_historicplay_game_name ON historicplay(game_name);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_bonus_system_game_name ON bonus_system(game_name);");

        // 5. CRIAÇÃO DAS CHAVES ESTRANGEIRAS
        _add_constraint_if_not_exists($pdo, 'users', 'fk_users_referral_level', 'FOREIGN KEY (level_id) REFERENCES referral_levels(id) ON DELETE RESTRICT');
        _add_constraint_if_not_exists($pdo, 'users', 'fk_users_referrer', 'FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE SET NULL');
        _add_constraint_if_not_exists($pdo, 'withdrawals', 'fk_withdrawals_user_id', 'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        _add_constraint_if_not_exists($pdo, 'commission_transactions', 'commission_transactions_user_id_fkey', 'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        _add_constraint_if_not_exists($pdo, 'commission_transactions', 'commission_transactions_referred_user_id_fkey', 'FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL');
        _add_constraint_if_not_exists($pdo, 'deposits', 'deposits_user_id_fkey', 'FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        _add_constraint_if_not_exists($pdo, 'historicplay', 'historicplay_user_id_fkey', 'FOREIGN KEY (user_id) REFERENCES users(id)');
        _add_constraint_if_not_exists($pdo, 'referral_deposits', 'referral_deposits_referrer_user_id_fkey', 'FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE');
        _add_constraint_if_not_exists($pdo, 'referral_deposits', 'referral_deposits_referred_user_id_fkey', 'FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE');
        _add_constraint_if_not_exists($pdo, 'transactions', 'transactions_user_id_fkey', 'FOREIGN KEY (user_id) REFERENCES users(id)');
        _add_constraint_if_not_exists($pdo, 'transactions', 'fk_transactions_withdrawal', 'FOREIGN KEY (withdrawal_id) REFERENCES withdrawals(id) ON DELETE SET NULL');
        _add_constraint_if_not_exists($pdo, 'historicplay', 'historicplay_game_name_fkey', 'FOREIGN KEY (game_name) REFERENCES games(name)');

        // 6. INSERÇÃO DOS DADOS INICIAIS
        // Insere os níveis de afiliação
        $pdo->exec("INSERT INTO referral_levels (id, name, min_active_indications, commission_rate, level_image_url, min_xp) VALUES (1, 'Bronze', 0, 0.050, 'https://raspagreen.com/assets/level_bronze.png', 0) ON CONFLICT (id) DO NOTHING;");
        $pdo->exec("INSERT INTO referral_levels (id, name, min_active_indications, commission_rate, level_image_url, min_xp) VALUES (2, 'Prata', 5, 0.100, 'https://raspagreen.com/assets/level_prata.png', 5000) ON CONFLICT (id) DO NOTHING;");
        $pdo->exec("INSERT INTO referral_levels (id, name, min_active_indications, commission_rate, level_image_url, min_xp) VALUES (3, 'Ouro', 20, 0.500, 'https://raspagreen.com/assets/level_ouro.png', 10000) ON CONFLICT (id) DO NOTHING;");

        // Insere os métodos de pagamento
        $pdo->exec("INSERT INTO payment_methods (id, name, provider_key, is_active, icon_url, description) VALUES (1, 'PIX', 'IronPay', TRUE, 'https://ik.imagekit.io/kyjz2djk3p/pix.png?updatedAt=1755565284446', 'Pagamento rápido e seguro.') ON CONFLICT (id) DO NOTHING;");
        $pdo->exec("INSERT INTO payment_methods (id, name, provider_key, is_active, icon_url, description) VALUES (2, 'PIX  ', 'ZeroOnePay', TRUE, 'https://ik.imagekit.io/kyjz2djk3p/pix.png?updatedAt=1755565284446', 'Opção de pagamento instantâneo.') ON CONFLICT (id) DO NOTHING;");
        $pdo->exec("INSERT INTO payment_methods (id, name, provider_key, is_active, icon_url, description) VALUES (3, 'PIX  ', 'PixUp', TRUE, 'https://ik.imagekit.io/kyjz2djk3p/pix.png?updatedAt=1755565284446', 'Opção de pagamento instantâneo.') ON CONFLICT (id) DO NOTHING;");

        // ✅ NOVO: Inserção dos jogos
        $pdo->exec("
            INSERT INTO games (name, bet_cost, prizes_json, win_chance_percent) VALUES
                ('Centavo da Sorte', 0.50, '[{\"name\": \"1.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png\", \"amount\": 1000.00}, {\"name\": \"700 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png\", \"amount\": 700.00}, {\"name\": \"500 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/500-REAIS.png\", \"amount\": 500.00}, {\"name\": \"200 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/200-REAIS.png\", \"amount\": 200.00}, {\"name\": \"Smartwatch D20 Shock\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_smartwatch_d20_shock.png?updatedAt=1757352245233\", \"amount\": 150.00}, {\"name\": \"100 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png\", \"amount\": 100.00}, {\"name\": \"PowerBank\", \"image\": \"https://ik.imagekit.io/kyjz2djk3p/powerbank.png\", \"amount\": 60.00}, {\"name\": \"50 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png\", \"amount\": 50.00}, {\"name\": \"20 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/20-reais.png\", \"amount\": 20.00}, {\"name\": \"15 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/15-reais.png\", \"amount\": 15.00}, {\"name\": \"10 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10-reais.png\", \"amount\": 10.00}, {\"name\": \"5 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5-reais.png\", \"amount\": 5.00}, {\"name\": \"4 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/4%20reais.png\", \"amount\": 4.00}, {\"name\": \"3 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/3%20reais.png\", \"amount\": 3.00}, {\"name\": \"2 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/2-reais.png\", \"amount\": 2.00}, {\"name\": \"1 Real\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1-real.png\", \"amount\": 1.00}, {\"name\": \"0,50 Centavos\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-CENTAVOS-2.png\", \"amount\": 0.50}]', 0.00),
                ('Sorte Instantanea', 1.00, '[{\"name\": \"Caixa de som JBL\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_jbl_boombox_3_black.png?updatedAt=1757352247599\", \"amount\": 2500.00}, {\"name\": \"iPhone 12\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_iphone_12.png?updatedAt=1757352244936\", \"amount\": 2500.00}, {\"name\": \"1.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png\", \"amount\": 1000.00}, {\"name\": \"Smartphone\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_c2_nk109.png?updatedAt=1757352240875\", \"amount\": 800.00}, {\"name\": \"700 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png\", \"amount\": 700.00}, {\"name\": \"Bola de futebol\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_ft_5_branca_e_preta.png?updatedAt=1757352243992\", \"amount\": 500.00}, {\"name\": \"Perfume 212\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_212_vip_black.png?updatedAt=1757352240894\", \"amount\": 399.00}, {\"name\": \"Camisa de time\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_camisa_do_seu_time.png?updatedAt=1757352240917\", \"amount\": 350.00}, {\"name\": \"Fone de ouvido\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_fone_de_ouvido_lenovo.png?updatedAt=1757352243935\", \"amount\": 220.00}, {\"name\": \"200 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/200-REAIS.png\", \"amount\": 200.00}, {\"name\": \"Copo Stanley\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_copo_t_rmico_stanley_preto.png?updatedAt=1757352242839\", \"amount\": 165.00}, {\"name\": \"100 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png\", \"amount\": 100.00}, {\"name\": \"PowerBank\", \"image\": \"https://ik.imagekit.io/kyjz2djk3p/powerbank.png\", \"amount\": 60.00}, {\"name\": \"50 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png\", \"amount\": 50.00}, {\"name\": \"Chinelo Havaianas\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_chinelo_havaianas_top_branco.png?updatedAt=1757352242753\", \"amount\": 35.00}, {\"name\": \"10 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10-reais.png\", \"amount\": 10.00}, {\"name\": \"5 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5-reais.png\", \"amount\": 5.00}, {\"name\": \"3 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/3%20reais.png\", \"amount\": 3.00}, {\"name\": \"2 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/2-reais.png\", \"amount\": 2.00}, {\"name\": \"1 Real\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1-real.png\", \"amount\": 1.00}]', 0.00),
                ('Raspadinha Suprema', 2.50, '[{\"name\": \"5.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5k.png\", \"amount\": 5000.00}, {\"name\": \"iPhone 15\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_azul.png?updatedAt=1757352247562\", \"amount\": 5000.00}, {\"name\": \"Notebook Dell G15\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_notebook_g15.png?updatedAt=1757352245208\", \"amount\": 4500.00}, {\"name\": \"PlayStation 5\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_playstation_5.png?updatedAt=1757352245144\", \"amount\": 4500.00}, {\"name\": \"Smart TV 4K\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_smart_tv_4k_55.png?updatedAt=1757352245308\", \"amount\": 3000.00}, {\"name\": \"Ipad 10º\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/iPad%2010%20(%202.800%20).png?updatedAt=1757365157701\", \"amount\": 2800.00}, {\"name\": \"Caixa de som JBL\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_jbl_boombox_3_black.png?updatedAt=1757352247599\", \"amount\": 2500.00}, {\"name\": \"AirPods 3ª gen.\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_airpods_3_gera_o.png?updatedAt=1757352240268\", \"amount\": 1900.00}, {\"name\": \"1.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png\", \"amount\": 1000.00}, {\"name\": \"Air Fryer\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_air_fryer.png?updatedAt=1757352240875\", \"amount\": 850.00}, {\"name\": \"700 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png\", \"amount\": 700.00}, {\"name\": \"500 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_ft_5_branca_e_preta.png?updatedAt=1757352243992\", \"amount\": 500.00}, {\"name\": \"Adaptador tipo C\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_adaptador_de_energia_usb_c.png?updatedAt=1757352240429\", \"amount\": 220.00}, {\"name\": \"200 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/200-REAIS.png\", \"amount\": 200.00}, {\"name\": \"Fone de ouvido\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_fone_de_ouvido_bluetooth.png?updatedAt=1757352244406\", \"amount\": 170.00}, {\"name\": \"Copo Stanley\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_copo_t_rmico_stanley_rosa.png?updatedAt=1757352242932\", \"amount\": 165.00}, {\"name\": \"Smartwatch D20\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_smartwatch_d20_shock.png?updatedAt=1757352245233\", \"amount\": 150.00}, {\"name\": \"100 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png\", \"amount\": 100.00}, {\"name\": \"PowerBank\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/PowerBank%20(%2060.00%20).png?updatedAt=1757374031442\", \"amount\": 60.00}, {\"name\": \"50 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png\", \"amount\": 50.00}, {\"name\": \"20 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/20-reais.png\", \"amount\": 20.00}, {\"name\": \"5 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5-reais.png\", \"amount\": 5.00}, {\"name\": \"2 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/2-reais.png\", \"amount\": 2.00}, {\"name\": \"1 Real\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1-real.png\", \"amount\": 1.00}]', 0.00),
                ('Raspa Relampago', 5.00, '[{\"name\": \"Churrasqueira a gás\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_churrasqueira_a_g_s_versia_gourmand.png?updatedAt=1757352242874\", \"amount\": 15000.00}, {\"name\": \"Moto Biz\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_biz_110i_vermelho.png?updatedAt=1757352246694\", \"amount\": 13000.00}, {\"name\": \"Moto Honda\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_pop_110i_branco.png?updatedAt=1757352247943\", \"amount\": 11500.00}, {\"name\": \"iPhone 15 Pro\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_256_gb_tit_nio_natural.png?updatedAt=1757352247590\", \"amount\": 11000.00}, {\"name\": \"10.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10k.png\", \"amount\": 10000.00}, {\"name\": \"Apple Watch\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_apple_watch_ultra_2_pulseira_loop_alpina_azul_p.png?updatedAt=1757352246522\", \"amount\": 9000.00}, {\"name\": \"Geladeira\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_geladeira_frost_free.png?updatedAt=1757352244155\", \"amount\": 7500.00}, {\"name\": \"Galaxy Z Flip5\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_galaxy_z_flip5_256_gb_creme.png?updatedAt=1757352247548\", \"amount\": 6000.00}, {\"name\": \"5.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5k.png\", \"amount\": 5000.00}, {\"name\": \"Xbox Series X\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_xbox_series_x.png?updatedAt=1757352246256\", \"amount\": 4500.00}, {\"name\": \"PlayStation 5\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_playstation_5.png\", \"amount\": 4500.00}, {\"name\": \"Lava-louças\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_lava_lou_a_samsung.png?updatedAt=1757352245130\", \"amount\": 4000.00}, {\"name\": \"700 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png\", \"amount\": 700.00}, {\"name\": \"500 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/500-REAIS.png\", \"amount\": 500.00}, {\"name\": \"Controle Xbox\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_controle_xbox_eletric_volt.png?updatedAt=1757352243106\", \"amount\": 500.00}, {\"name\": \"Controle\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_controle_dualsense_playstation_midnight_black.png?updatedAt=1757352243087\", \"amount\": 470.00}, {\"name\": \"200 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/200-REAIS.png\", \"amount\": 200.00}, {\"name\": \"Fone de ouvido\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_fone_de_ouvido_bluetooth.png?updatedAt=1757352244406\", \"amount\": 170.00}, {\"name\": \"100 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png\", \"amount\": 100.00}, {\"name\": \"50 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png\", \"amount\": 50.00}, {\"name\": \"15 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/15-reais.png\", \"amount\": 15.00}, {\"name\": \"10 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10-reais.png\", \"amount\": 10.00}, {\"name\": \"5 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5-reais.png\", \"amount\": 5.00}, {\"name\": \"2 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/2-reais.png\", \"amount\": 2.00}]', 0.00),
                ('Raspadinha Magica', 50.00, '[{\"name\": \"20.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/20k-removebg-preview.png?updatedAt=1757374679331\", \"amount\": 20000.00}, {\"name\": \"Moto CG\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_cg_160_start_prata_met_lico.png?updatedAt=1757352246895\", \"amount\": 16500.00}, {\"name\": \"Moto Biz\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_biz_110i_vermelho.png?updatedAt=1757352246694\", \"amount\": 13000.00}, {\"name\": \"Moto Honda\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_pop_110i_branco.png?updatedAt=1757352247943\", \"amount\": 11500.00}, {\"name\": \"iPhone 15 Pro\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_256_gb_tit_nio_natural.png?updatedAt=1757352247590\", \"amount\": 11000.00}, {\"name\": \"10.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10k.png\", \"amount\": 10000.00}, {\"name\": \"iPhone 15 Pro Max\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_max_256_gb_nio_preto.png?updatedAt=1757352247645\", \"amount\": 9500.00}, {\"name\": \"Geladeira\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_geladeira_frost_free.png?updatedAt=1757352244155\", \"amount\": 7500.00}, {\"name\": \"Apple Watch\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_apple_watch_ultra_2_pulseira_loop_alpina_azul_p.png?updatedAt=1757352246522\", \"amount\": 9000.00}, {\"name\": \"Churrasqueira a gás\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_churrasqueira_a_g_s_versia_gourmand.png?updatedAt=1757352242874\", \"amount\": 5000.00}, {\"name\": \"iPhone 15\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_azul.png?updatedAt=1757352247562\", \"amount\": 5000.00}, {\"name\": \"5.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5k.png\", \"amount\": 5000.00}, {\"name\": \"PlayStation 5\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_playstation_5.png\", \"amount\": 4500.00}, {\"name\": \"iPhone 12\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_iphone_12.png?updatedAt=1757352244936\", \"amount\": 2500.00}, {\"name\": \"AirPods 3ª gen.\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_airpods_3_gera_o.png?updatedAt=1757352240268\", \"amount\": 1900.00}, {\"name\": \"Air Force 1\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/Air%20Force%201%20x%20AMBU%20(%201.700%20).png?updatedAt=1757364636476\", \"amount\": 1700.00}, {\"name\": \"Air Jordan 1\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/Air%20Force%201%20Low%20Retr%20(%201.200%20).png?updatedAt=1757364636241\", \"amount\": 1200.00}, {\"name\": \"Air Jordan 1\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/Air%20Jordan%201%20Low%20P%20(%201.100%20).png?updatedAt=1757364636253\", \"amount\": 1100.00}, {\"name\": \"1.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png\", \"amount\": 1000.00}, {\"name\": \"700 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png\", \"amount\": 700.00}, {\"name\": \"500 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/500-REAIS.png\", \"amount\": 500.00}, {\"name\": \"100 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png\", \"amount\": 100.00}, {\"name\": \"50 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png\", \"amount\": 50.00}, {\"name\": \"Capinha transp.\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_capinha_trasparente_iphone_15_pro_max.png?updatedAt=1757352241566\", \"amount\": 30.00}, {\"name\": \"5 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5-reais.png\", \"amount\": 5.00}, {\"name\": \"1 Real\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1-real.png\", \"amount\": 1.00}]', 0.00),
                ('Raspe e Ganhe', 100.00, '[{\"name\": \"50.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/400.000.00%20mil%20reais.png?updatedAt=1757374225104\", \"amount\": 50000.00}, {\"name\": \"Churrasqueira\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_churrasqueira_cer_mica_carv_o.png?updatedAt=1757352242852\", \"amount\": 20000.00}, {\"name\": \"20.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/20k-removebg-preview.png?updatedAt=1757374679331\", \"amount\": 20000.00}, {\"name\": \"Moto CG\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_cg_160_start_prata_met_lico.png?updatedAt=1757352246895\", \"amount\": 16500.00}, {\"name\": \"Moto Biz\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_biz_110i_vermelho.png?updatedAt=1757352246694\", \"amount\": 13000.00}, {\"name\": \"Moto Honda\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_pop_110i_branco.png?updatedAt=1757352247943\", \"amount\": 11500.00}, {\"name\": \"iPhone 15 Pro Max\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_max_256_gb_nio_preto.png?updatedAt=1757352247645\", \"amount\": 11000.00}, {\"name\": \"10.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10k.png\", \"amount\": 10000.00}, {\"name\": \"iPhone 15 Pro\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_pro_256_gb_tit_nio_natural.png?updatedAt=1757352247590\", \"amount\": 7500.00}, {\"name\": \"Galaxy Z Flip5\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_galaxy_z_flip5_256_gb_creme.png?updatedAt=1757352247548\", \"amount\": 6000.00}, {\"name\": \"iPhone 15\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_iphone_15_azul.png?updatedAt=1757352247562\", \"amount\": 5000.00}, {\"name\": \"Churrasqueira a gás\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_churrasqueira_a_g_s_performance_340s.png?updatedAt=1757352242785\", \"amount\": 5000.00}, {\"name\": \"5.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/5k.png\", \"amount\": 5000.00}, {\"name\": \"PlayStation 5\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_playstation_5.png?updatedAt=1757352245144\", \"amount\": 4500.00}, {\"name\": \"Motorola Edge 40\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/variant_edge_40_neo_256_gb_black_beauty.png?updatedAt=1757352247482\", \"amount\": 2800.00}, {\"name\": \"1.000 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/1K.png\", \"amount\": 1000.00}, {\"name\": \"700 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/700.png\", \"amount\": 700.00}, {\"name\": \"500 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/500-REAIS.png\", \"amount\": 500.00}, {\"name\": \"Controle\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_controle_dualsense_playstation_midnight_black.png?updatedAt=1757352243087\", \"amount\": 470.00}, {\"name\": \"Fone de ouvido\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/item_fone_de_ouvido_bluetooth.png?updatedAt=1757352244406\", \"amount\": 170.00}, {\"name\": \"100 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/100-reais.png\", \"amount\": 100.00}, {\"name\": \"50 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/50-reais.png\", \"amount\": 50.00}, {\"name\": \"10 Reais\", \"image\": \"https://ik.imagekit.io/3kbnnws8u/PRIZES/10-reais.png\", \"amount\": 10.00}]', 0.00)
            ON CONFLICT (name) DO UPDATE SET
                bet_cost = EXCLUDED.bet_cost,
                prizes_json = EXCLUDED.prizes_json,
                win_chance_percent = EXCLUDED.win_chance_percent;
        ");

        // ✅ NOVO: Inserção dos bônus iniciais
        $pdo->exec("
            INSERT INTO bonus_system (game_name, faturamento_meta, bonus_amount, current_faturamento, current_bonus_paid, is_bonus_active) VALUES
                ('Centavo da Sorte', 10.00, 4.00, 0.00, 4.00, FALSE),
                ('Sorte Instantanea', 30.00, 10.00, 25.00, 10.00, FALSE),
                ('Raspadinha Suprema', 100.00, 30.00, 137.50, 30.00, FALSE),
                ('Raspa Relampago', 20.00, 15.00, 60.00, 15.00, FALSE),
                ('Raspadinha Magica', 100.00, 10.00, 150.00, 1.00, FALSE),
                ('Raspe e Ganhe', 100.00, 200.00, 700.00, 200.00, FALSE)
            ON CONFLICT (game_name) DO NOTHING;
        ");

        // 7. CRIAÇÃO DO TRIGGER
        $pdo->exec("DROP TRIGGER IF EXISTS users_set_updated_at ON users;");
        $pdo->exec("CREATE TRIGGER users_set_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION set_updated_at();");

        // 8. CRIAÇÃO DA TABELA MARCADORA
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$markerTable} (install_date TIMESTAMP WITH TIME ZONE DEFAULT NOW());");

        // 9. COMMIT DA TRANSAÇÃO
        $pdo->commit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new Exception("Falha ao inicializar o schema do banco de dados: " . $e->getMessage(), 500, $e);
    }
}

/**
 * [Função Interna] Adiciona uma coluna apenas se ela não existir.
 */
function _add_column_if_not_exists(PDO $pdo, string $table, string $columnName, string $columnDef): void
{
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = :table_name AND column_name = :column_name");
    $stmt->execute([':table_name' => $table, ':column_name' => $columnName]);
    if ($stmt->fetchColumn() === false) {
        $pdo->exec("ALTER TABLE public.{$table} ADD COLUMN {$columnName} {$columnDef}");
    }
}

/**
 * [Função Interna] Adiciona uma chave estrangeira (constraint) apenas se ela não existir.
 */
function _add_constraint_if_not_exists(PDO $pdo, string $table, string $constraintName, string $constraintDef): void
{
    $stmt = $pdo->prepare("SELECT 1 FROM pg_constraint WHERE conname = :constraint_name");
    $stmt->execute([':constraint_name' => $constraintName]);
    if ($stmt->fetchColumn() === false) {
        $pdo->exec("ALTER TABLE public.{$table} ADD CONSTRAINT {$constraintName} {$constraintDef}");
    }
}

/**
 * [Função Interna] Adiciona uma restrição UNIQUE a uma coluna se ela não existir.
 */
function _add_unique_constraint_if_not_exists(PDO $pdo, string $table, string $constraintName, string $columnName): void
{
    $stmt = $pdo->prepare("SELECT 1 FROM pg_constraint WHERE conname = :constraint_name AND conrelid = (SELECT oid FROM pg_class WHERE relname = :table_name)");
    $stmt->execute([':constraint_name' => $constraintName, ':table_name' => $table]);
    if ($stmt->fetchColumn() === false) {
        $pdo->exec("ALTER TABLE public.{$table} ADD CONSTRAINT {$constraintName} UNIQUE ({$columnName})");
    }
}