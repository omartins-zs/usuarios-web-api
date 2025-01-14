<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportacaoController extends Controller
{
    public function importarArquivo(Request $request)
    {
        // Logando a requisição para verificar os cabeçalhos e o que foi recebido
        Log::info('Requisição recebida na API do Projeto 2', ['headers' => $request->headers->all()]);
        Log::debug('Dados recebidos', ['request_data' => $request->all()]);

        try {
            // Validando se o arquivo foi enviado e é do tipo correto
            $request->validate([
                'arquivo' => 'required|file|mimes:csv,xlsx,xls,txt|max:10240', // Permitindo também arquivos .txt, se necessário
                'tabela'  => 'required|string', // Validando que a tabela foi enviada
            ]);

            $file = $request->file('arquivo');
            $tabela = $request->input('tabela');

            // Logando o nome do arquivo recebido
            Log::info('Arquivo recebido', ['nome_original' => $file->getClientOriginalName()]);
            // Preparando o arquivo para ser enviado ao próximo serviço (se necessário)
            $response = Http::attach('arquivo', file_get_contents($file), 'arquivo.' . $file->getClientOriginalExtension())
                ->post("http://127.0.0.1:8003/api/importar/{$tabela}", [
                    'tabela' => $tabela
                ]);

            // Verificando a resposta do Projeto 3
            if ($response->successful()) {
                Log::info('Arquivo enviado ao Importador com sucesso', ['response' => $response->body()]);
                return response()->json(['message' => 'Arquivo enviado ao Importador com sucesso!']);
            }

            // Caso a resposta não seja bem-sucedida
            Log::error('Erro ao processar no Projeto 3', ['response' => $response->body()]);
            return response()->json(['message' => 'Erro ao processar o arquivo no Importador.'], 500);
        } catch (\Exception $e) {
            // Logando qualquer erro que ocorrer no processamento
            Log::error('Erro no processamento', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Erro no processamento.', 'detalhes' => $e->getMessage()], 500);
        }
    }
}
