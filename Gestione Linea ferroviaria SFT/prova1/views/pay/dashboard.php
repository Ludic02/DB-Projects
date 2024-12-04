<!DOCTYPE html>
<html>
<head>
    <title>PaySteam - Dashboard</title>
    <link rel="stylesheet" href="assets/css/pay.css">
</head>
<body>
    <div class="pay-dashboard">
        <h1>Il mio conto PaySteam</h1>

        <?php if (Session::hasMessage()): ?>
            <div class="alert <?php echo Session::getErrorMessage() ? 'alert-danger' : 'alert-success'; ?>">
                <?php 
                    echo Session::getErrorMessage() ?: Session::getSuccessMessage(); 
                ?>
            </div>
        <?php endif; ?>

        <div class="balance-card">
            <h2>Saldo disponibile</h2>
            <div class="balance-amount">
                € <?php echo number_format($balance, 2, ',', '.'); ?>
            </div>
            <a href="index.php?page=pay&action=ricarica" class="action-button">Ricarica</a>
        </div>

        <div class="card-list">
            <div class="header-actions">
                <h2>Le mie carte</h2>
                <a href="index.php?page=pay&action=addCard" class="action-button">Aggiungi carta</a>
            </div>

            <?php if (empty($cards)): ?>
                <p>Nessuna carta registrata</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Scadenza</th>
                            <th>Data aggiunta</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cards as $card): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($card['numero_mascherato']); ?></td>
                                <td><?php echo htmlspecialchars($card['scadenza']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($card['data_aggiunta'])); ?></td>
                                <td>
                                    <form action="index.php?page=pay&action=removeCard" method="POST">
                                        <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                        <button type="submit" class="action-button danger" 
                                                onclick="return confirm('Sei sicuro di voler rimuovere questa carta?')">
                                            Rimuovi
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if (!empty($transactions)): ?>
    <div class="card-list">
        <h2>Transazioni recenti</h2>
        <div class="transaction-list">
            <?php foreach ($transactions as $transaction): ?>
                <div class="transaction-item">
                    <div class="transaction-info">
                        <div class="transaction-name">
                            <?php echo htmlspecialchars($transaction['descrizione']); ?>
                        </div>
                        <div class="transaction-date">
                            <?php echo date('d/m/Y H:i', strtotime($transaction['data_creazione'])); ?>
                        </div>
                    </div>
                    <div class="transaction-amount">
                        € <?php echo number_format($transaction['importo'], 2, ',', '.'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
    </div>
</body>
</html>