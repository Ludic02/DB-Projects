<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>PaySteam - Sistema di Pagamento Sicuro</h3>
                </div>
                <div class="card-body">
                    <!-- Sezione Introduttiva -->
                    <div class="mb-4">
                        <h4>Come funziona</h4>
                        <p>PaySteam è il sistema di pagamento integrato per la Ferrovia Turistica che ti permette di:</p>
                        <ul>
                            <li>Acquistare biglietti in modo sicuro e veloce</li>
                            <li>Gestire il tuo saldo personale</li>
                            <li>Memorizzare le tue carte di credito in modo sicuro</li>
                            <li>Monitorare tutte le tue transazioni</li>
                        </ul>
                    </div>

                    <!-- Vantaggi della Registrazione -->
                    <div class="mb-4">
                        <h4>Vantaggi dell'Account</h4>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-wallet text-primary me-2"></i>Gestione Saldo</h5>
                                        <p>Ricarica il tuo conto e paga i biglietti senza inserire ogni volta i dati della carta.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-credit-card text-primary me-2"></i>Carte Salvate</h5>
                                        <p>Memorizza le tue carte in modo sicuro per pagamenti più veloci.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-history text-primary me-2"></i>Storico Transazioni</h5>
                                        <p>Monitora tutti i tuoi acquisti e movimenti in tempo reale.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5><i class="fas fa-shield-alt text-primary me-2"></i>Sicurezza</h5>
                                        <p>I tuoi dati sono protetti con i più alti standard di sicurezza.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Condizioni di Utilizzo -->
                    <div class="mb-4">
                        <h4>Condizioni di Utilizzo</h4>
                        <div class="alert alert-info">
                            <ul class="mb-0">
                                <li>È necessario avere almeno 18 anni</li>
                                <li>È richiesto un indirizzo email valido</li>
                                <li>Devi possedere una carta di credito valida</li>
                                <li>Il saldo minimo per la ricarica è di €10</li>
                                <li>Il saldo massimo per la ricarica è di €1000</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Call to Action -->
                    <div class="text-center">
                        <a href="index.php?page=auth&action=register" class="btn btn-primary btn-lg mb-2">
                            <i class="fas fa-user-plus me-2"></i>
                            Registrati Ora
                        </a>
                        <p class="text-muted">
                            Hai già un account? 
                            <a href="index.php?page=auth&action=login">Accedi qui</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar con FAQ -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Domande Frequenti</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    È sicuro memorizzare la mia carta?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Sì, utilizziamo sistemi di crittografia avanzati per proteggere i tuoi dati di pagamento.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Come funziona la ricarica del conto?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Puoi ricaricare il tuo conto usando una carta di credito. L'importo minimo è €10, il massimo €1000.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Posso richiedere un rimborso?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Sì, puoi richiedere il rimborso dei biglietti non utilizzati secondo le nostre politiche di cancellazione.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Come posso contattare l'assistenza?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Il nostro servizio clienti è disponibile via email e telefono nei giorni feriali.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>