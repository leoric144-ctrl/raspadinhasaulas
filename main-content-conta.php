<?php
// perfil-conta.php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    echo "<p>Sessão inválida. Por favor, faça login novamente.</p>";
    return;
}

require_once __DIR__ . '/db.php';

$total_withdrawn_approved = 0;

try {
    $pdo = db();

    // Consulta principal, removendo total_withdrawn da tabela users
    $stmt = $pdo->prepare(
        "SELECT email, name, username, phone, document, total_deposited, cashback_earnings
         FROM users WHERE id = ?"
    );
    $stmt->execute([$_SESSION['user_id']]);
    $account_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$account_user) {
        header('Location: /logout.php');
        exit();
    }

    // NOVA CONSULTA para buscar apenas o total de saques aprovados
    // CORRIGIDO: Adicionado a função LOWER() para buscar o status independentemente da capitalização
    $stmt_withdrawals = $pdo->prepare(
        "SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE user_id = ? AND LOWER(status) = 'approved'"
    );
    $stmt_withdrawals->execute([$_SESSION['user_id']]);
    $total_withdrawn_approved = $stmt_withdrawals->fetchColumn();

} catch (PDOException $e) {
    die("Erro ao carregar os dados da conta.");
}

function format_brl_parts($value) {
    $formatted = number_format(floatval($value), 2, ',', '.');
    return ['currency' => 'R$', 'value' => $formatted];
}

function format_cpf($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', (string) $cpf);
    if (strlen($cpf) != 11) {
        return null;
    }
    return vsprintf('%s.%s.%s-%s', str_split($cpf, 3));
}

function format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', (string) $phone);
    $length = strlen($phone);
    if ($length < 10) {
        return htmlspecialchars($phone ?: 'Não definido');
    }
    if ($length == 10) {
        return vsprintf('(%s) %s-%s', [substr($phone, 0, 2), substr($phone, 2, 4), substr($phone, 6, 4)]);
    }
    return vsprintf('(%s) %s-%s', [substr($phone, 0, 2), substr($phone, 2, 5), substr($phone, 7, 4)]);
}
?>

<style>
    .account-content { padding: 2rem; }
    .account-content h2 { color: #f0f0f0; font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; margin-top: 1.5rem; }
    .account-content h2:first-child { margin-top: 0; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
    .stat-card { background-color: #1F1F1F; border: 1px solid #27272a; border-radius: 10px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; }
    .stat-card .info .title { color: #a0a0a0; font-size: 0.9rem; margin-bottom: 0.25rem; }
    .stat-card .info .value { color: #f0f0f0; font-size: 1.5rem; font-weight: 500; }
    .stat-card .info .value .currency { color: #28e504; margin-right: 0.25rem; }
    .stat-card .icon-wrapper { background-color: #2a2a2e; border-radius: 20%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
    .stat-card .icon-wrapper svg { color: #28e504; width: 24px; height: 24px; }
    .info-list { background-color: #1F1F1F; border: 1px solid #27272a; border-radius: 10px; overflow: hidden; }
    .info-item { display: flex; flex-direction: column; align-items: stretch; padding: 1rem 1.25rem; border-bottom: 1px solid #27272a; gap: 0.5rem; }
    .info-item:last-child { border-bottom: none; }
    .info-item .label { color: #f0f0f0; font-weight: 500; font-size: 0.9rem; }
    .content-wrapper { display: flex; align-items: center; gap: 0.75rem; }
    .value-wrapper { flex-grow: 1; display: flex; align-items: center; gap: 0.5rem; color: #a0a0a0; }
    .value-wrapper svg { width: 16px; height: 16px; }
    .info-item .value-input { background-color: #27272a; border: 1px solid #3f3f46; color: #f0f0f0; border-radius: 8px; padding: 0.6rem 0.75rem; width: 100%; font-size: 1rem; height: 42px; }
    .info-item .value-input:focus { outline: none; border-color: #28e504; }
    .edit-controls { display: flex; gap: 0.5rem; }
    .edit-button, .save-button, .cancel-button { background-color: #28e504; color: #111; border: none; border-radius: 8px; padding: 0.5rem 1rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap; height: 42px; }
    .edit-button:hover, .save-button:hover { opacity: 0.9; }
    .cancel-button { background-color: #4b5563; color: #f0f0f0; }
    .cancel-button:hover { background-color: #6b7280; }
    .hidden { display: none; }

    @media (max-width: 768px) {
        .account-content { padding: 1.5rem; }
        .account-content h2 { font-size: 1.1rem; margin-bottom: 0.75rem; }
        .stat-card { padding: 1rem; }
        .stat-card .info .value { font-size: 1.3rem; }
        .info-item { padding: 1rem; }
        .content-wrapper { flex-direction: column; align-items: stretch; gap: 0.75rem; }
        .edit-controls { justify-content: flex-end; }
    }
    @media (max-width: 480px) {
        .account-content { padding: 1rem; }
        .stats-grid { grid-template-columns: 1fr; }
        .edit-controls { display: grid; grid-template-columns: 1fr 1fr; }
    }
</style>

<div class="account-content">
    <h2>Estatísticas</h2>
    <div class="stats-grid">
        <?php
        $stats_data = [
            'total_deposited' => [
                'title' => 'Total Depositado',
                'value' => $account_user['total_deposited'],
                'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a3 3 0 1 0 3 3 3 3 0 0 0-3-3Zm0 4a1 1 0 1 1 1-1 1 1 0 0 1-1 1Zm-.71-6.29a1 1 0 0 0 .33.21.94.94 0 0 0 .76 0 1 1 0 0 0 .33-.21L15 7.46A1 1 0 1 0 13.54 6l-.54.59V3a1 1 0 0 0-2 0v3.59L10.46 6A1 1 0 0 0 9 7.46ZM19 15a1 1 0 1 0-1 1 1 1 0 0 0 1-1Zm1-7h-3a1 1 0 0 0 0 2h3a1 1 0 0 1 1 1v8a1 1 0 0 1-1-1H4a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h3a1 1 0 0 0 0-2H4a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h16a3 3 0 0 0 3-3v-8a3 3 0 0 0-3-3ZM5 15a1 1 0 1 0 1-1 1 1 0 0 0-1 1Z"></path></svg>'
            ],
            'total_withdrawn' => [
                'title' => 'Total Retirado',
                'value' => $total_withdrawn_approved,
                'icon' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1ZM7 20v-2a2 2 0 0 1 2 2Zm10 0h-2a2 2 0 0 1 2-2Zm0-4a4 4 0 0 0-4 4h-2a4 4 0 0 0-4-4V8h10Zm4-6h-2V7a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3H3V4h18Zm-9 5a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm0-4a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"></path></svg>'
            ],
            'cashback_earnings' => [
                'title' => 'Ganho em Cashback',
                'value' => $account_user['cashback_earnings'],
                'icon' => '<svg fill="none" viewBox="0 0 48 48"><path fill="currentColor" fill-rule="evenodd" d="M24.039 6c-4.517 0-8.632 1.492-11.067 2.711q-.33.165-.616.322c-.378.206-.7.398-.956.567l2.77 4.078 1.304.519c5.096 2.571 11.93 2.571 17.027 0l1.48-.768L36.6 9.6a16 16 0 0 0-1.689-.957C32.488 7.437 28.471 6 24.04 6Zm-6.442 4.616a25 25 0 0 1-2.901-.728c2.281-1.013 5.68-2.088 9.343-2.088 2.537 0 4.936.516 6.92 1.17-2.325.327-4.806.882-7.17 1.565-1.86.538-4.034.48-6.192.081Zm15.96 5.064-.246.124c-5.606 2.828-13.042 2.828-18.648 0l-.233-.118C6.008 24.927-.422 41.997 24.039 41.997S41.913 24.61 33.557 15.68ZM23 24a2 2 0 1 0 0 4zm2-2v-1h-2v1a4 4 0 0 0 0 8v4c-.87 0-1.611-.555-1.887-1.333a1 1 0 1 0-1.885.666A4 4 0 0 0 23 36v1h2v-1a4 4 0 0 0 0-8v-4c.87 0 1.611.555 1.887 1.333a1 1 0 1 0 1.885-.666A4 4 0 0 0 25 22Zm0 8v4a2 2 0 1 0 0-4Z" clip-rule="evenodd"></path></svg>'
            ]
        ];

        foreach ($stats_data as $key => $stat):
        $formatted_value = format_brl_parts($stat['value']); ?>
            <div class="stat-card">
                <div class="info"><div class="title"><?= $stat['title'] ?></div><div class="value"><span class="currency"><?= $formatted_value['currency'] ?></span><?= $formatted_value['value'] ?></div></div>
                <div class="icon-wrapper"><?= $stat['icon'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Informações Pessoais</h2>
    <div class="info-list">
        <?php foreach (['email' => ['label' => 'Email', 'type' => 'email', 'icon' => '<svg fill="currentColor" viewBox="0 0 16 16"><path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/></svg>'], 'username' => ['label' => 'Username', 'type' => 'text', 'icon' => '<svg fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg>'], 'phone' => ['label' => 'Telefone', 'type' => 'tel', 'icon' => '<svg fill="currentColor" viewBox="0 0 16 16"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.612l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/></svg>'], 'document' => ['label' => 'Documento', 'type' => 'text', 'icon' => '<svg width="1em" height="1em" fill="none" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M4.5 3.75a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V6.75a3 3 0 0 0-3-3zm4.125 3a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Zm-3.873 8.703a4.126 4.126 0 0 1 7.746 0 .75.75 0 0 1-.351.92 7.5 7.5 0 0 1-3.522.877 7.5 7.5 0 0 1-3.522-.877.75.75 0 0 1-.351-.92ZM15 8.25a.75.75 0 0 0 0 1.5h3.75a.75.75 0 0 0 0-1.5zM14.25 12a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H15a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3.75a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"></path></svg>']] as $field => $details): ?>
            <div class="info-item" data-field="<?= $field ?>">
                <div class="label"><?= $details['label'] ?></div>
                <div class="content-wrapper">
                    <div class="value-wrapper">
                        <?= $details['icon'] ?>
                        <span class="value-text">
                            <?php
                            if ($field === 'document') {
                                $cpf_formatado = format_cpf($account_user['document']);
                                echo $cpf_formatado ? $cpf_formatado : 'Informar CPF';
                            } elseif ($field === 'phone') {
                                echo format_phone($account_user['phone']);
                            } else {
                                echo htmlspecialchars($account_user[$field] ?? 'Não definido');
                            }
                            ?>
                        </span>
                        <input type="<?= $details['type'] ?>" class="value-input hidden" value="<?= htmlspecialchars($account_user[$field] ?? '') ?>">
                    </div>
                    <div class="edit-controls">

                        <?php if ($field !== 'document' || empty($account_user['document'])): ?>
                            <button class="edit-button">
                                <svg fill="currentColor" viewBox="0 0 16 16" width="16" height="16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.5-.5H3v-.5a.5.5 0 0 1 .5-.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5z"/></svg>
                                Editar
                            </button>
                        <?php endif; ?>

                        <button class="save-button hidden">Salvar</button>
                        <button class="cancel-button hidden">Cancelar</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="form-feedback" style="margin-top: 1rem;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.info-item').forEach(item => {
        const editButton = item.querySelector('.edit-button');

        // Se o botão não existir (caso do CPF já preenchido), o script para aqui para este item.
        if (!editButton) {
            return;
        }

        const saveButton = item.querySelector('.save-button');
        const cancelButton = item.querySelector('.cancel-button');
        const valueText = item.querySelector('.value-text');
        const valueInput = item.querySelector('.value-input');
        const valueWrapper = item.querySelector('.value-wrapper');
        const field = item.dataset.field;

        // Pega o valor bruto do input para restaurar se cancelar
        let originalRawValue = valueInput.value;

        editButton.addEventListener('click', () => {
            // Se o valor for "Não definido" ou "Informar CPF", limpa o campo de input
            if (['Não definido', 'Informar CPF'].includes(valueText.textContent.trim())) {
                valueInput.value = '';
            } else {
                valueInput.value = originalRawValue;
            }

            item.querySelector('.content-wrapper').prepend(valueInput);
            valueWrapper.classList.add('hidden');
            editButton.classList.add('hidden');
            valueInput.classList.remove('hidden');
            saveButton.classList.remove('hidden');
            cancelButton.classList.remove('hidden');
            valueInput.focus();
        });

        const exitEditMode = () => {
            item.querySelector('.content-wrapper').prepend(valueWrapper);
            valueWrapper.classList.remove('hidden');
            editButton.classList.remove('hidden');
            valueInput.classList.add('hidden');
            saveButton.classList.add('hidden');
            cancelButton.classList.add('hidden');
            valueInput.value = originalRawValue; // Restaura sempre o valor bruto original
        };

        cancelButton.addEventListener('click', exitEditMode);

        saveButton.addEventListener('click', async () => {
            const newValue = valueInput.value.trim();
            const feedbackDiv = document.getElementById('form-feedback');

            saveButton.disabled = true;
            saveButton.innerHTML = 'Salvando...';

            try {
                const response = await fetch('/api/update-profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ field: field, value: newValue })
                });

                const result = await response.json();

                feedbackDiv.style.color = result.ok ? '#28e504' : '#ff4d6a';
                feedbackDiv.textContent = result.message || result.error;

                setTimeout(() => { feedbackDiv.textContent = ''; }, 3000);

                if (result.ok) {
                    valueText.textContent = result.formattedValue || result.newValue;
                    originalRawValue = result.newValue; // Atualiza o valor bruto original com o novo valor salvo
                    exitEditMode();

                    // Se o campo salvo for o documento, recarrega a página para esconder o botão "Editar"
                    if(field === 'document'){
                        window.location.reload();
                    }
                }
            } catch (error) {
                feedbackDiv.style.color = '#ff4d6a';
                feedbackDiv.textContent = 'Ocorreu um erro de comunicação.';
                setTimeout(() => { feedbackDiv.textContent = ''; }, 3000);
            } finally {
                saveButton.disabled = false;
                saveButton.innerHTML = 'Salvar';
            }
        });

        // Máscaras de input
        if (field === 'document') {
            valueInput.setAttribute('maxlength', '14');
            valueInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, "");
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d)/, "$1.$2");
                value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                e.target.value = value;
            });
        }

        if (field === 'phone') {
            valueInput.setAttribute('maxlength', '15');
            valueInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.replace(/^(\d\d)(\d{5})(\d{4}).*/, '($1) $2-$3');
                } else if (value.length > 5) {
                    value = value.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/^(\d\d)(\d*)/, '($1) $2');
                } else {
                    value = value.replace(/^(\d*)/, '($1');
                }
                e.target.value = value;
            });
        }
    });
});
</script>