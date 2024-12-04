<?php
// File: views/trains/my_bookings.php
?>
<div class="container">
    <h2>Le Mie Prenotazioni</h2>

    <?php if(!empty($bookings)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Treno</th>
                        <th>Da</th>
                        <th>A</th>
                        <th>Orario</th>
                        <th>Posti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $booking): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($booking['data'])); ?></td>
                            <td><?php echo $booking['treno_id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['stazione_partenza']); ?></td>
                            <td><?php echo htmlspecialchars($booking['stazione_arrivo']); ?></td>
                            <td>
                                <?php 
                                    echo substr($booking['partenza'], 0, 5) . ' - ' . 
                                         substr($booking['arrivo'], 0, 5); 
                                ?>
                            </td>
                            <td><?php echo $booking['numero_posti']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Non hai ancora effettuato prenotazioni.
        </div>
    <?php endif; ?>

    <a href="index.php?page=trains" class="btn btn-primary">Prenota un viaggio</a>
</div>