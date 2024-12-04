<div class="container mt-4">
    <h2>Orari e Materiale Rotabile</h2>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Informazioni sulla linea -->
    <div class="alert alert-info mb-4">
        <h4>Informazioni sulla Linea</h4>
        <p><strong>Tipo:</strong> Linea a binario unico</p>
        <p><strong>Lunghezza totale:</strong> 54,680 km</p>
        <p><strong>Stazioni servite:</strong></p>
        <ul class="list-unstyled">
            <?php foreach ($stazioni as $stazione): ?>
                <li>- <?php echo htmlspecialchars($stazione['nome']); ?> 
                    (Km <?php echo number_format($stazione['km'], 3, ',', '.'); ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if (empty($trains)): ?>
        <div class="alert alert-info">
            Nessun treno disponibile al momento.
        </div>
    <?php else: ?>
        <?php 
        $grouped_trains = [];
        foreach ($trains as $train) {
            if (strtolower($train['tipo']) === 'locomotiva') {
                continue;
            }
            $grouped_trains[$train['tipo']][] = $train;
        }
        ?>

        <?php foreach ($grouped_trains as $tipo => $tipo_treni): ?>
            <div class="mb-4">
                <h3><?php echo ucfirst($tipo); ?></h3>
                <div class="row">
                    <?php foreach ($tipo_treni as $train): ?>
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($train['nome']); ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Dettagli del treno -->
                                        <div class="col-md-3">
                                            <h6>Dettagli Tecnici:</h6>
                                            <p>
                                                <strong>Anno:</strong> <?php echo $train['anno_serie']; ?><br>
                                                <strong>Posti:</strong> <?php echo $train['posti_totali']; ?><br>
                                                <strong>Velocità max:</strong> <?php echo $train['velocita_max']; ?> km/h
                                            </p>
                                        </div>
                                        
                                        <!-- Tabella orari -->
                                        <div class="col-md-9">
                                            <h6>Orari di Percorrenza:</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Stazione</th>
                                                            <th>Km</th>
                                                            <th>Orario</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $stmt = $db->prepare("
                                                            SELECT s.nome as stazione, s.km, TIME_FORMAT(o.orario, '%H:%i') as orario
                                                            FROM sft_orario o
                                                            JOIN sft_stazione s ON o.stazione_id = s.id
                                                            WHERE o.treno_id = ?
                                                            ORDER BY s.km ASC");
                                                        $stmt->execute([$train['id']]);
                                                        $orari = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                        
                                                        foreach ($orari as $orario): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($orario['stazione']); ?></td>
                                                                <td><?php echo number_format($orario['km'], 3, ',', '.'); ?></td>
                                                                <td><?php echo $orario['orario']; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (Session::isLoggedIn()): ?>
                                        <div class="mt-3">
                                            <a href="index.php?page=trains&action=prenota&id=<?php echo $train['id']; ?>" 
                                               class="btn btn-success">
                                                Prenota
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Nota informativa -->
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i> 
            La velocità massima prevista in linea è di 50 km/h su tutto il percorso.
            <?php if (!Session::isLoggedIn()): ?>
                <br>
                <strong>Nota:</strong> Per effettuare prenotazioni è necessario 
                <a href="index.php?page=auth&action=login" class="alert-link">effettuare il login</a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>