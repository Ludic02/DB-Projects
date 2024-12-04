<?php
if (!Session::isLoggedIn() || Session::getUserType() !== 'esercizio') {
    header('Location: index.php');
    exit;
}

// Debug dei dati ricevuti
error_log("Dati ricevuti nella vista:");
error_log("Materiale rotabile: " . (isset($materiale_rotabile) ? count($materiale_rotabile) : 'non definito'));
error_log("Orari: " . (isset($orari) ? count($orari) : 'non definito'));
error_log("Stazioni: " . (isset($stazioni) ? count($stazioni) : 'non definito'));
error_log("Richieste: " . (isset($richieste) ? count($richieste) : 'non definito'));
error_log("Convogli: " . (isset($convogli) ? count($convogli) : 'non definito'));

// Inizializzazione variabili
$materiale_rotabile = $materiale_rotabile ?? [];
$orari = $orari ?? [];
$stazioni = $stazioni ?? [];
$richieste = $richieste ?? [];
$convogli = $convogli ?? [];
?>

<h2>Dashboard Backoffice di Esercizio</h2>

<!-- Sezione Gestione Materiale Rotabile -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Gestione Materiale Rotabile</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalComponiConvoglio">
            <i class="fas fa-plus"></i> Componi Nuovo Convoglio
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Posti Totali</th>
                        <th>Stato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materiale_rotabile as $materiale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($materiale['nome']); ?></td>
                            <td><?php echo htmlspecialchars($materiale['tipo']); ?></td>
                            <td><?php echo $materiale['posti_totali']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $materiale['disponibile'] ? 'success' : 'warning'; ?>">
                                    <?php echo $materiale['disponibile'] ? 'Disponibile' : 'In Uso'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="mb-0">Convogli Composti</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome Convoglio</th>
                        <th>Composizione</th>
                        <th>Posti Totali</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($convogli)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nessun convoglio composto</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($convogli as $convoglio): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($convoglio['nome'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($convoglio['composizione'] ?? ''); ?></td>
                                <td><?php echo isset($convoglio['posti_totali']) ? intval($convoglio['posti_totali']) : 0; ?></td>
                                <td>
                                    <form action="index.php?page=backoffice&action=eliminaConvoglio" method="post">
                                        <input type="hidden" name="convoglio_id" value="<?php echo intval($convoglio['id']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" name="conferma_elimina" value="1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sezione Orari -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Gestione Orari</h3>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAggiungiOrario">
            <i class="fas fa-plus"></i> Aggiungi Orario
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Treno</th>
                        <th>Stazione</th>
                        <th>Tipo</th>
                        <th>Orario</th>
                        <th>Giorni</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orari)): ?>
                        <?php foreach ($orari as $orario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($orario['nome_treno'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($orario['nome_stazione'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($orario['tipo'] ?? ''); ?></td>
                                <td><?php echo $orario['orario_effettivo'] ? date('H:i', strtotime($orario['orario_effettivo'])) : ''; ?></td>
<td><?php echo htmlspecialchars($orario['giorni_effettivi'] ?? ''); ?></td>
                                <td>
    <div class="btn-group" role="group">
        
        
        
<!-- Tasto Modifica -->
<button type="button" 
        class="btn btn-primary btn-sm me-1" 
        onclick="appFunctions.caricaDatiModifica(<?php echo $orario['id']; ?>)">
    <i class="fas fa-edit"></i>
</button>

<!-- Tasto Elimina -->
        <form action="index.php?page=backoffice&action=eliminaOrario" method="post" class="d-inline">
            <input type="hidden" name="orario_id" value="<?php echo $orario['id']; ?>">
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </div>
</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Nessun orario configurato</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<!-- Sezione Richieste Pendenti -->
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">Richieste Pendenti dall'Amministrazione</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tipo</th>
                        <th>Treno</th>
                        <th>Data Prevista</th>
                        <th>Motivo</th>
                        <th>Azioni</th>
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
                                <button type="button" class="btn btn-success btn-sm"
                                        onclick="rispondiRichiesta(<?php echo $richiesta['id']; ?>)">
                                    Rispondi
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- Modal Componi Convoglio -->
<div class="modal fade" id="modalComponiConvoglio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Componi Nuovo Convoglio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?page=backoffice&action=componiConvoglio" method="post">
                <div class="modal-body">
                    <!-- Nome Convoglio -->
                    <div class="mb-3">
                        <label for="nome_convoglio" class="form-label">Nome Convoglio</label>
                        <input type="text" class="form-control" id="nome_convoglio" name="nome_convoglio" required>
                    </div>

                    <!-- Locomotiva/Automotrice -->
                    <div class="mb-3">
                        <label for="locomotiva_id" class="form-label">Locomotiva/Automotrice</label>
                        <select class="form-select" id="locomotiva_id" name="locomotiva_id" required>
                            <option value="">Seleziona locomotiva/automotrice</option>
                            <?php 
                            foreach ($materiale_rotabile as $materiale): 
                                if (($materiale['tipo'] === 'locomotiva' || $materiale['tipo'] === 'automotrice') && $materiale['disponibile']): 
                            ?>
                                <option value="<?php echo $materiale['id']; ?>">
                                    <?php echo htmlspecialchars($materiale['nome']); ?>
                                    <?php if ($materiale['tipo'] === 'automotrice'): ?>
                                        (<?php echo $materiale['posti_totali']; ?> posti)
                                    <?php endif; ?>
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>

                    <!-- Carrozze -->
                    <div class="mb-3">
                        <label class="form-label">Carrozze (seleziona almeno una)</label>
                        <?php
                        $carrozze_disponibili = array_filter($materiale_rotabile, function($m) {
                            return ($m['tipo'] === 'carrozza' || $m['tipo'] === 'bagagliaio') && $m['disponibile'];
                        });
                        ?>
                        <?php if (empty($carrozze_disponibili)): ?>
                            <p class="text-muted">Nessuna carrozza disponibile</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($carrozze_disponibili as $carrozza): ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="carrozze[]" 
                                                   value="<?php echo $carrozza['id']; ?>" 
                                                   id="carrozza_<?php echo $carrozza['id']; ?>">
                                            <label class="form-check-label" for="carrozza_<?php echo $carrozza['id']; ?>">
                                                <?php echo htmlspecialchars($carrozza['nome']); ?>
                                                <small class="text-muted">(<?php echo $carrozza['posti_totali']; ?> posti)</small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="submit_type" value="componi_convoglio">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Componi Convoglio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Aggiungi Orario -->

<div class="modal fade" id="modalAggiungiOrario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aggiungi Nuovo Orario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?page=backoffice&action=aggiungiOrario" method="post">
                <div class="modal-body">
                    <input type="hidden" name="submit_type" value="aggiungi_orario">
                    
                    <!-- Selezione convoglio -->
                    <div class="mb-3">
                        <label for="convoglio_id" class="form-label">Convoglio</label>
                        <select name="treno_id" id="convoglio_id" class="form-select" required>
                            <option value="">Seleziona convoglio</option>
                            <?php foreach ($convogli as $convoglio): ?>
                                <option value="<?php echo htmlspecialchars($convoglio['id']); ?>">
                                    <?php echo htmlspecialchars($convoglio['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Selezione stazione e tipo -->
                    <div class="mb-3">
                        <label for="stazione_id" class="form-label">Stazione</label>
                        <select name="stazione_id" id="stazione_id" class="form-select" required>
                            <option value="">Seleziona stazione</option>
                            <?php foreach ($stazioni as $stazione): ?>
                                <option value="<?php echo htmlspecialchars($stazione['id']); ?>">
                                    <?php echo htmlspecialchars($stazione['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Orario -->
                    <div class="mb-3">
                        <label for="orario" class="form-label">Orario</label>
                        <input type="time" name="orario" id="orario" class="form-control" required>
                    </div>

                    <!-- Tipo (partenza/arrivo) -->
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="partenza">Partenza</option>
                            <option value="arrivo">Arrivo</option>
                        </select>
                    </div>

                    <!-- Data del Viaggio -->
                    <div class="mb-3">
                        <label for="booking_date" class="form-label">Data del Viaggio</label>
                        <input type="date" name="booking_date" id="booking_date" 
                               class="form-control" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo date('Y-m-d'); ?>">
                        <div class="invalid-feedback">
                            Seleziona una data valida
                        </div>
                    </div>
                </div> <!-- Chiusura modal-body -->
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Aggiungi Orario</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Modifica Orario -->
<div class="modal fade" id="modalModificaOrario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifica Orario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formModificaOrario" action="index.php?page=backoffice&action=modificaOrario" method="post">
                <div class="modal-body">
                    <input type="hidden" name="orario_id" id="mod_orario_id">

                    <div class="mb-3">
                        <label for="mod_orario" class="form-label">Orario</label>
                        <input type="time" name="orario" id="mod_orario" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="mod_data" class="form-label">Data del Viaggio</label>
                        <input type="date" name="giorni" id="mod_data" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal Rispondi Richiesta -->
<div class="modal fade" id="modalRispondiRichiesta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rispondi alla Richiesta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?page=backoffice&action=rispondiRichiesta" method="post" id="formRispondiRichiesta">
                <input type="hidden" name="richiesta_id" id="richiesta_id_risposta" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="azione" class="form-label">Azione</label>
                        <select name="azione" id="azione" class="form-select" required>
                            <option value="">Seleziona azione</option>
                            <option value="APPROVA">Approva</option>
                            <option value="RIFIUTA">Rifiuta</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nota_risposta" class="form-label">Nota di Risposta</label>
                        <textarea name="nota_risposta" id="nota_risposta" class="form-control" rows="3" required
                            placeholder="Inserisci una nota per la risposta"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Invia Risposta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rispondiRichiesta(richiestaId) {
    console.log('Richiesta ID:', richiestaId);
    document.getElementById('richiesta_id_risposta').value = richiestaId;
    var modal = new bootstrap.Modal(document.getElementById('modalRispondiRichiesta'));
    modal.show();
}
</script>

<!-- Modal Dettaglio Orari Convoglio -->
<div class="modal fade" id="modalDettaglioOrariConvoglio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Orari Convoglio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table" id="tabellaOrariConvoglio">
                        <thead>
                            <tr>
                                <th>Stazione Partenza</th>
                                <th>Orario Partenza</th>
                                <th>Stazione Arrivo</th>
                                <th>Orario Arrivo</th>
                                <th>Giorni</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const appFunctions = {
    caricaDatiModifica: function(id) {
    console.log('Caricamento dati modifica per ID:', id);
    
    // Previeni errori se l'elemento non esiste
    const modal = document.getElementById('modalModificaOrario');
    if (!modal) {
        console.error('Modal non trovato');
        return;
    }

    const idInput = document.getElementById('mod_orario_id');
    const orarioInput = document.getElementById('mod_orario');
    const dataInput = document.getElementById('mod_data');

    if (!idInput || !orarioInput || !dataInput) {
        console.error('Campi del form non trovati');
        return;
    }

    // Imposta l'ID
    idInput.value = id;

    fetch('index.php?page=backoffice&action=getOrario&id=' + id, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        cache: 'no-store'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Errore nella risposta del server');
        }
        return response.json();
    })
    .then(data => {
        console.log('Dati ricevuti:', data);
        if (data.error) {
            throw new Error(data.message);
        }
        
        // Imposta i valori nei campi
        orarioInput.value = data.orario || '';
        dataInput.value = data.giorni || '';
        
        // Apri il modal usando Bootstrap
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    })
    .catch(error => {
        console.error('Errore:', error);
        alert('Errore nel caricamento dei dati: ' + error.message);
    });
},

    rispondiRichiesta: function(richiestaId) {
        console.log('Richiesta ID:', richiestaId);
        document.getElementById('richiesta_id_risposta').value = richiestaId;
        var modal = new bootstrap.Modal(document.getElementById('modalRispondiRichiesta'));
        modal.show();
    },  
    
    mostraOrariConvoglio: function(convoglioId) {
        fetch(`index.php?page=backoffice&action=getOrariConvoglio&id=${convoglioId}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#tabellaOrariConvoglio tbody');
                tbody.innerHTML = '';
                
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nessun orario configurato</td></tr>';
                } else {
                    data.forEach(orario => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${orario.stazione_partenza}</td>
                                <td>${orario.orario_partenza}</td>
                                <td>${orario.stazione_arrivo}</td>
                                <td>${orario.orario_arrivo}</td>
                                <td>${orario.giorni}</td>
                            </tr>
                        `;
                    });
                }
                
                new bootstrap.Modal(document.getElementById('modalDettaglioOrariConvoglio')).show();
            })
            .catch(error => {
                console.error('Errore nel recupero degli orari:', error);
                alert('Errore nel recupero degli orari del convoglio');
            });
    }
};

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Modal form handler
    const modalForm = document.querySelector('#modalModificaOrario form');
    if (modalForm) {
        modalForm.addEventListener('submit', (e) => {
            const formData = {
                id: document.getElementById('mod_orario_id').value,
                orario: document.getElementById('mod_orario').value,
                data: document.getElementById('mod_data').value
            };
            console.log('Modal form submitted:', formData);
        });
    }

    // Main form handler
    const mainForm = document.getElementById('formModificaOrario');
    if (mainForm) {
        mainForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = {
                orario_id: e.target.elements['orario_id'].value,
                orario: e.target.elements['orario'].value,
                giorni: e.target.elements['giorni'].value
            };

            console.log('Form data before submission:', formData);

            // Validation
            if (Object.values(formData).some(value => !value)) {
                alert('Tutti i campi sono obbligatori');
                return;
            }

            // Submit form if validation passes
            e.target.submit();
        });
    }
});

</script>