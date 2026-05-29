<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RssSource;

class OpmlController extends Controller
{
    /**
     * Importiere Feeds aus einer OPML-Datei.
     */
    public function import(Request $request)
    {      
        $validated = $request->validate([
            'opml_file' => ['required', 'file', 'mimes:xml'],
        ]);

        try {
            return $this->processOpmlFile($validated['opml_file']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Fehler beim Importieren: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verarbeitet die OPML-Datei und fügt die Feeds zur Datenbank hinzu.
     */
    private function processOpmlFile($file)
    {
        libxml_use_internal_errors(true);
        
        $xmlContent = simplexml_load_string(file_get_contents($file));

        if (!$xmlContent) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new \Exception(
                'OPML-Parse-Fehler: ' . implode(', ', array_map(
                    fn($error) => $error->message,
                    $errors
                ))
            );
        }

        $importedCount = 0;
        $this->parseOpmlOutline($xmlContent->body->outline ?? [], $importedCount);

        return response()->json([
            'message' => "$importedCount RSS-Feeds erfolgreich importiert.",
            'count' => $importedCount,
        ], 201);
    }

    /**
     * Rekursive Methode, die alle Feeds aus einem OPML-Outline extrahiert.
     */
    private function parseOpmlOutline($outlines, &$importedCount)
    {
        if (empty($outlines)) {
            return;
        }

        $userId = auth()->id();
        $urls = [];

        foreach ($outlines as $outline) {
            if (isset($outline['xmlUrl'])) {
                $url = (string) $outline['xmlUrl'];
                $urls[$url] = (string) ($outline['text'] ?? 'Unbenannt');
            }

            // Rekursiv verschachtelte outlines verarbeiten
            if (isset($outline->outline)) {
                $this->parseOpmlOutline($outline->outline, $importedCount);
            }
        }

        // Batch-Import: Nur nicht vorhandene Feeds erstellen
        if (!empty($urls)) {
            foreach ($urls as $url => $name) {
                RssSource::firstOrCreate(
                    ['url' => $url],
                    [
                        'user_id' => $userId,
                        'name' => $name,
                        'is_active' => true,
                    ]
                );
                $importedCount++;
            }
        }
    }
}

?>