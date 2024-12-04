<?php
class BookingController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index() {
        $this->list();
    }

    public function list() {
        // Recupera tutte le prenotazioni dell'utente
        $stmt = $this->db->prepare("
            SELECT p.*,
                   t.nome as nome_treno,
                   s1.nome as stazione_partenza_nome,
                   s2.nome as stazione_arrivo_nome,
                   o1.orario as orario_partenza,
                   o2.orario as orario_arrivo
            FROM sft_prenotazione p
            JOIN sft_treno t ON p.id_treno = t.id
            JOIN sft_stazione s1 ON p.stazione_partenza = s1.id
            JOIN sft_stazione s2 ON p.stazione_arrivo = s2.id
            JOIN sft_orario o1 ON o1.id_treno = t.id AND o1.id_stazione = p.stazione_partenza
            JOIN sft_orario o2 ON o2.id_treno = t.id AND o2.id_stazione = p.stazione_arrivo
            WHERE p.id_utente = ?
            ORDER BY p.data_prenotazione DESC, p.data_creazione DESC");

        $stmt->execute([Session::getUserId()]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include 'views/bookings/list.php';
    }

    public function view() {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=bookings&action=list');
            exit;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

        // Recupera dettagli prenotazione
        $stmt = $this->db->prepare("
            SELECT p.*,
                   t.nome as nome_treno,
                   s1.nome as stazione_partenza_nome,
                   s2.nome as stazione_arrivo_nome,
                   o1.orario as orario_partenza,
                   o2.orario as orario_arrivo
            FROM sft_prenotazione p
            JOIN sft_treno t ON p.id_treno = t.id
            JOIN sft_stazione s1 ON p.stazione_partenza = s1.id
            JOIN sft_stazione s2 ON p.stazione_arrivo = s2.id
            JOIN sft_orario o1 ON o1.id_treno = t.id AND o1.id_stazione = p.stazione_partenza
            JOIN sft_orario o2 ON o2.id_treno = t.id AND o2.id_stazione = p.stazione_arrivo
            WHERE p.id = ? AND p.id_utente = ?");

        $stmt->execute([$id, Session::getUserId()]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $_SESSION['error_message'] = "Prenotazione non trovata";
            header('Location: index.php?page=bookings&action=list');
            exit;
        }

        include 'views/bookings/view.php';
    }
}