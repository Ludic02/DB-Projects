<div class="container mt-4">
    <div class="row">
        <!-- Saldo e Carte -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Il tuo conto</h4>
                </div>
                <div class="card-body">
                    <h2 class="text-primary mb-4">
                        € <?php echo number_format($account['saldo'] ?? 0, 2, ',', '.'); ?>
                    </h2>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCardModal">
                        <i class="fas fa-credit-card me-2"></i>
                        Aggiungi Carta
                    </button>
                </div>
            </div>

            <!-- Carte Salvate -->
            <?php if (!empty($carte)): ?>
            <div class="card">
                <div class="card-header">
                    <h4>Le tue carte</h4>
                </div>
                <div class="card-body">
                    <?php foreach($carte as $carta): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="fas fa-credit-card me-2"></i>
                            **** <?php echo substr($carta['numero_carta'], -4); ?>
                            <small class="text-muted">
                                (scad. <?php echo $carta['scadenza']; ?>)
                            </small>
                        </div>
                        <form action="index.php?page=pay&action=removeCard" method="POST" class="d-inline">
                            <input type="hidden" name="card_id" value="<?php echo $carta['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Movimenti -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Ultimi movimenti</h4>
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
                                <?php foreach($movimenti as $movimento): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($movimento['data_creazione'])); ?></td>
                                    <td><?php echo htmlspecialchars($movimento['descrizione']); ?></td>
                                    <td class="<?php echo $movimento['tipo_movimento'] == 'ENTRATA' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ($movimento['tipo_movimento'] == 'ENTRATA' ? '+' : '-'); ?>
                                        € <?php echo number_format($movimento['importo'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $movimento['stato'] == 'COMPLETED' ? 'success' : 'warning'; ?>">
                                            <?php echo $movimento['stato']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aggiungi Carta -->
<div class="modal fade" id="addCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=pay&action=addCard" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi Carta di Credito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Numero Carta</label>
                        <input type="text" name="numero_carta" class="form-control" required
                               pattern="\d{16}" maxlength="16" placeholder="1234567890123456">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Scadenza</label>
                        <input type="text" name="scadenza" class="form-control" required
                               pattern="(0[1-9]|1[0-2])\/[0-9]{2}" placeholder="MM/YY">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>