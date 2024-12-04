<div class="container mt-4">
    <div class="card mb-4">
        <div class="card-header">
            <h2><?php echo htmlspecialchars($train['nome']); ?></h2>
        </div>
        <div class="card-body">
            <!-- Dettagli tecnici -->
            <div class="mb-4">
                <h3>Dettagli Tecnici</h3>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>Tipo:</strong> <?php echo ucfirst($train['tipo']); ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Anno di costruzione:</strong> <?php echo $train['anno_serie']; ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Posti a sedere:</strong> <?php echo $train['posti_totali']; ?>
                    </li>
                    <li class="list-group-item">
                        <strong>Velocità massima:</strong> <?php echo $train['velocita_max']; ?> km/h
                    </li>
                </ul>
            </div>

            <!-- Storia del materiale rotabile -->
            <div class="mb-4">
                <h3>Storia del Materiale Rotabile</h3>
                <div class="alert alert-info">
                    <?php 
                    if (isset($storiaRotabile[$train['tipo']][$train['anno_serie']])) {
                        echo $storiaRotabile[$train['tipo']][$train['anno_serie']];
                    }
                    ?>
                </div>
            </div>

            <!-- Linea e fermate -->
            <div class="mb-4">
                <h3>Percorso e Fermate</h3>
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> Linea a binario unico
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Stazione</th>
                                <th>Km</th>
                                <th>Orario</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fermate as $fermata): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fermata['stazione']); ?></td>
                                    <td><?php echo number_format($fermata['km'], 3, ',', ''); ?></td>
                                    <td><?php echo $fermata['orario']; ?></td>
                                    <td><?php echo htmlspecialchars($fermata['descrizione']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <strong>Lunghezza totale linea:</strong> 54,680 km
                </div>
            </div>

            <?php if (Session::isLoggedIn()): ?>
                <!-- Sezione prenotazione -->
                <div class="mt-4" id="prenota">
                    <h3>Prenota questo treno</h3>
                    <form action="index.php?page=trains&action=prenota" method="POST">
                        <input type="hidden" name="id_treno" value="<?php echo $train['id']; ?>">
                        <!-- Form di prenotazione qui -->
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pulsante torna indietro -->
    <a href="index.php?page=trains" class="btn btn-secondary mb-4">
        <i class="fas fa-arrow-left"></i> Torna alla lista treni
    </a>
</div>