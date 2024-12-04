<?php
// Debug all'inizio della vista
error_log("Dashboard View - Starting render");
error_log("Dashboard View - Balance available: " . (isset($balance) ? 'yes' : 'no'));
error_log("Dashboard View - Cards available: " . (isset($cards) ? 'yes' : 'no'));

// Verifica accesso
if (!Session::isLoggedIn()) {
    header('Location: index.php?page=auth&action=login');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PaySteam - Dashboard</title>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Il mio Conto PaySteam</h2>

        <?php if (Session::getErrorMessage()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars(Session::getErrorMessage()); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (Session::getSuccessMessage()): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars(Session::getSuccessMessage()); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!isset($balance) || !isset($cards)): ?>
            <div class="alert alert-danger">
                <?php 
                error_log("Dashboard View - Missing required variables");
                echo "Errore nel caricamento dei dati. Riprova più tardi.";
                ?>
            </div>
        <?php else: ?>
            <!-- Sezione Saldo -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Saldo disponibile</h3>
                        </div>
                        <div class="card-body">
                            <h2 class="text-primary mb-3">€ <?php echo number_format($balance, 2, ',', '.'); ?></h2>
                            <a href="index.php?page=pay&action=ricarica" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Ricarica
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sezione Carte -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Le mie Carte</h3>
                    <a href="index.php?page=pay&action=addCard" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Aggiungi Carta
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($cards)): ?>
                        <p class="text-muted">Nessuna carta registrata</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                                <form action="index.php?page=pay&action=removeCard" method="POST" class="d-inline">
                                                    <input type="hidden" name="carta_id" value="<?php echo $card['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Sei sicuro di voler rimuovere questa carta?')">
                                                        <i class="fas fa-trash"></i> Rimuovi
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sezione Transazioni Recenti -->
            <?php if (isset($transactions) && !empty($transactions)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Transazioni Recenti</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Descrizione</th>
                                        <th>Importo</th>
                                        <th>Stato</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($transaction['data_creazione'])); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['descrizione']); ?></td>
                                            <td class="<?php echo $transaction['importo'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                € <?php echo number_format(abs($transaction['importo']), 2, ',', '.'); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $transaction['stato'] === 'COMPLETED' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo htmlspecialchars($transaction['stato']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
    // Nascondi automaticamente gli alert dopo 5 secondi
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert && typeof(alert.remove) === 'function') {
                    alert.remove();
                }
            });
        }, 5000);
    });
    </script>
</body>
</html>