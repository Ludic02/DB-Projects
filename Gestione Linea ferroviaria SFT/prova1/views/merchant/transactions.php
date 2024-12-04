<div class="container mt-4">
    <h2>Storico Movimenti</h2>

    <div class="card mt-4">
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <p class="text-center">Nessuna transazione trovata</p>
            <?php else: ?>
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
                                    <td><?php echo htmlspecialchars($transaction['data_formattata']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['descrizione']); ?></td>
                                    <td class="<?php echo $transaction['importo'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        €<?php echo number_format(abs($transaction['importo']), 2, ',', '.'); ?>
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
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Torna alla Dashboard
        </a>
    </div>
</div>