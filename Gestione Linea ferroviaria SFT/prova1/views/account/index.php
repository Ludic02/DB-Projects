<?php
// Recupera il saldo aggiornato
$stmt = $this->db->prepare("
    SELECT pu.saldo 
    FROM pay_utenti pu 
    JOIN sys_utente su ON su.email = pu.email 
    WHERE su.id = ?
");
$stmt->execute([Session::getUserId()]);
$account_data = $stmt->fetch(PDO::FETCH_ASSOC);
$saldo = $account_data['saldo'] ?? 0;
?>

<div class="container mt-4">
    <div class="row">
        <!-- Sezione principale -->
        <div class="col-md-8">
            <h2>Il mio conto</h2>
            
            <!-- Card Saldo -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title">Saldo disponibile</h5>
                            <h2 class="text-primary">€<?php echo number_format($saldo, 2, ',', '.'); ?></h2>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ricaricaModal">
                                <i class="fas fa-plus-circle me-2"></i>
                                Ricarica conto
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sezione Transazioni -->
            <div class="card">
                <div class="card-header">
                    <h4>Ultime transazioni</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrizione</th>
                                    <th>Importo</th>
                                    <th>Stato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach($transactions as $trans): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($trans['created_at']); ?></td>
                                            <td><?php echo htmlspecialchars($trans['descrizione']); ?></td>
                                            <td class="<?php echo $trans['importo'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo ($trans['importo'] > 0 ? '+' : ''); ?>
                                                €<?php echo number_format(abs($trans['importo']), 2, ',', '.'); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $trans['stato'] === 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo htmlspecialchars($trans['stato']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nessuna transazione disponibile
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Card Carte di Credito -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Le tue carte</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($cards)): ?>
                        <?php foreach($cards as $card): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <i class="fas fa-credit-card me-2"></i>
                                    **** <?php echo htmlspecialchars(substr($card['numero_carta'], -4)); ?>
                                    <br>
                                    <small class="text-muted">
                                        Scade: <?php echo htmlspecialchars($card['scadenza']); ?>
                                    </small>
                                </div>
                                <form action="index.php?page=pay&action=removeCard" method="POST" 
                                      onsubmit="return confirm('Sei sicuro di voler rimuovere questa carta?');">
                                    <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-3">Nessuna carta memorizzata</p>
                    <?php endif; ?>

                    <a href="index.php?page=pay&action=addCard" class="btn btn-primary w-100">
                        <i class="fas fa-credit-card me-2"></i>
                        Aggiungi Carta
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ricarica -->
<div class="modal fade" id="ricaricaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=pay&action=ricarica" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ricarica conto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Importo da ricaricare</label>
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" name="importo" class="form-control" required 
                                   min="10" max="1000" step="10" value="50">
                        </div>
                        <small class="text-muted">Minimo €10, massimo €1000</small>
                    </div>

                    <?php if (!empty($cards)): ?>
                        <div class="mb-3">
                            <label class="form-label">Seleziona la carta per il pagamento</label>
                            <select name="metodo_pagamento" class="form-select" required>
                                <?php foreach($cards as $card): ?>
                                    <option value="<?php echo $card['id']; ?>">
                                        **** <?php echo htmlspecialchars(substr($card['numero_carta'], -4)); ?>
                                        (scade: <?php echo htmlspecialchars($card['scadenza']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aggiungi prima una carta per poter effettuare la ricarica.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary" <?php echo empty($cards) ? 'disabled' : ''; ?>>
                        Ricarica ora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alert per messaggi di successo/errore -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>