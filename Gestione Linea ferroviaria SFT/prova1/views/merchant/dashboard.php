<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Dashboard Esercente</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Saldo -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>Saldo disponibile</h5>
                            <h2 class="text-primary">€<?php echo number_format(Session::getUserBalance(), 2, ',', '.'); ?></h2>
                        </div>
                    </div>
                </div>
                <!-- Azioni rapide -->
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>Azioni rapide</h5>
                            <a href="index.php?page=merchant&action=transactions" class="btn btn-primary mb-2 w-100">
                                <i class="fas fa-list me-2"></i>Visualizza Movimenti
                            </a>
                            <a href="index.php?page=merchant&action=withdraw" class="btn btn-outline-primary w-100">
                                <i class="fas fa-money-bill-transfer me-2"></i>Autorizza Transazione
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transazioni Recenti -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Transazioni Recenti</h5>
                            <small><a href="index.php?page=merchant&action=transactions">Vedi tutte</a></small>
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
                                        <?php if (!empty($recentTransactions)): ?>
                                            <?php foreach ($recentTransactions as $transaction): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($transaction['data_creazione'])); ?></td>
                                                    <td><?php echo htmlspecialchars($transaction['descrizione']); ?></td>
                                                    <td class="<?php echo $transaction['importo'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                                        €<?php echo number_format(abs($transaction['importo']), 2, ',', '.'); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $transaction['stato'] === 'COMPLETED' ? 'success' : 'warning'; ?>">
                                                            <?php echo $transaction['stato']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Nessuna transazione recente</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(Session::hasMessage()): ?>
        <div class="alert <?php echo Session::getErrorMessage() ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show mt-3">
            <?php echo Session::getErrorMessage() ?: Session::getSuccessMessage(); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>