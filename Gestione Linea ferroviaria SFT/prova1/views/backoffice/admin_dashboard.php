<?php
if (!Session::isLoggedIn() || Session::getUserType() !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<style>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1040;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1050;
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    overflow-y: auto;
    outline: 0;
}

.modal.fade.show {
    display: block;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 0.5rem;
    pointer-events: none;
    transform: translate(0, 0);
}

@media (min-width: 576px) {
    .modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
    }
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,0.2);
    border-radius: 0.3rem;
    outline: 0;
}

.modal-backdrop.show {
    opacity: 0.5;
}
</style>

<h2>Backoffice Amministrativo</h2>

<?php if (isset($_SESSION['show_cessazione_form']) && isset($_SESSION['cessazione_treno'])): ?>
    <div class="modal fade show" style="display: block;" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma Richiesta Cessazione</h5>
                </div>
                <form action="index.php?page=backoffice&action=richiediCessazione" method="post">
                    <div class="modal-body">
                        <p>Stai richiedendo la cessazione del treno: 
                            <strong><?php echo htmlspecialchars($_SESSION['cessazione_treno']['nome_treno']); ?></strong>
                        </p>
                        
                        <input type="hidden" name="treno_id" 
                               value="<?php echo $_SESSION['cessazione_treno']['id_treno']; ?>">
                        
                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo della richiesta</label>
                            <textarea name="motivo" id="motivo" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="index.php?page=backoffice" class="btn btn-secondary">Annulla</a>
                        <button type="submit" name="conferma_cessazione" class="btn btn-danger">
                            Conferma Cessazione
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Occupazione Treni</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRichiediStraordinario">
            <i class="fas fa-plus"></i> Richiedi Treno Straordinario
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($treni)): ?>
            <p class="text-muted">Nessun treno disponibile</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Treno</th>
                            <th>Composizione</th>
                            <th>Posti Totali</th>
                            <th>Posti Occupati</th>
                            <th>% Occupazione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($treni as $treno): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($treno['nome_treno']); ?></td>
                                <td><?php echo htmlspecialchars($treno['composizione'] ?? '-'); ?></td>
                                <td><?php echo $treno['posti_totali']; ?></td>
                                <td><?php echo $treno['posti_occupati']; ?></td>
                                <td>
                                    <?php 
                                    $percentuale = $treno['posti_totali'] > 0 
                                        ? round(($treno['posti_occupati'] / $treno['posti_totali']) * 100, 1) 
                                        : 0;
                                    ?>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo $percentuale > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $percentuale; ?>%">
                                            <?php echo $percentuale; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($treno['posti_occupati'] == 0): ?>
                                        <form action="index.php?page=backoffice&action=richiediCessazione" method="post" class="d-inline">
                                            <input type="hidden" name="treno_id" value="<?php echo $treno['id_treno']; ?>">
                                            <button type="submit" name="richiedi_cessazione" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Richiedi Cessazione
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Richieste Inviate</h3>
    </div>
    <div class="card-body">
        <?php if (empty($richieste)): ?>
            <p class="text-muted">Nessuna richiesta inviata</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data Richiesta</th>
                            <th>Tipo</th>
                            <th>Treno</th>
                            <th>Data Prevista</th>
                            <th>Motivo</th>
                            <th>Stato</th>
                            <th>Risposta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($richieste as $richiesta): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($richiesta['data_richiesta'])); ?></td>
                                <td><?php echo htmlspecialchars($richiesta['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($richiesta['nome_treno']); ?></td>
                                <td>
                                    <?php 
                                    echo isset($richiesta['data_prevista']) 
                                        ? date('d/m/Y', strtotime($richiesta['data_prevista'])) 
                                        : '-';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($richiesta['motivo']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $richiesta['stato'] === 'PENDING' ? 'warning' : 
                                            ($richiesta['stato'] === 'APPROVATA' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php echo htmlspecialchars($richiesta['stato']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($richiesta['nota_risposta'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Richiedi Straordinario -->
<div class="modal fade" id="modalRichiediStraordinario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Richiedi Treno Straordinario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?page=backoffice&action=richiediStraordinario" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="treno_id" class="form-label">Treno</label>
                        <select name="treno_id" id="treno_id" class="form-select" required>
                            <?php foreach ($treni as $treno): ?>
                                <option value="<?php echo $treno['id_treno']; ?>">
                                    <?php echo htmlspecialchars($treno['nome_treno']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="data_richiesta" class="form-label">Data Prevista</label>
                        <input type="date" name="data_richiesta" id="data_richiesta" 
                               class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo della Richiesta</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Invia Richiesta</button>
                </div>
            </form>
        </div>
    </div>
</div>