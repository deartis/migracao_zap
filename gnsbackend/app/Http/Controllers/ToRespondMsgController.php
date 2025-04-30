<?php

namespace App\Http\Controllers;

class ToRespondMsgController extends Controller
{
    /**
     * Mostrar a view principal do chat
     */
    public function index()
    {
        return view('pages.live');
    }

    /**
     * Obter todos os contatos do WhatsApp
     */
    public function getContacts()
    {
        try {
            // Aqui você usaria sua instância configurada de whatsapp-web.js para obter os contatos
            // Este é um exemplo de como seria a implementação

            // $whatsapp = app('whatsapp');
            // $contacts = $whatsapp->getContacts();

            // Dados de exemplo para demonstração
            $contacts = [
                [
                    'id' => '1',
                    'name' => 'João Silva',
                    'lastMessage' => 'Olá, tudo bem?',
                    'lastMessageTime' => '09:45',
                    'unreadCount' => 2,
                    'status' => 'online'
                ],
                [
                    'id' => '2',
                    'name' => 'Maria Oliveira',
                    'lastMessage' => 'Vamos marcar aquela reunião',
                    'lastMessageTime' => 'ontem',
                    'unreadCount' => 0
                ],
                [
                    'id' => '3',
                    'name' => 'Pedro Santos',
                    'lastMessage' => 'O documento foi enviado',
                    'lastMessageTime' => '10:30',
                    'unreadCount' => 1
                ],
                [
                    'id' => '4',
                    'name' => 'Grupo Família',
                    'lastMessage' => 'Alguém: Vamos almoçar domingo?',
                    'lastMessageTime' => 'ontem',
                    'unreadCount' => 0
                ],
                [
                    'id' => '5',
                    'name' => 'Ana Clara',
                    'lastMessage' => 'Obrigada pelas informações',
                    'lastMessageTime' => '08:15',
                    'unreadCount' => 0
                ]
            ];

            return response()->json($contacts);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar contatos do WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível carregar os contatos'], 500);
        }
    }

    /**
     * Obter mensagens de um contato específico
     */
    public function getMessages($contactId)
    {
        try {
            // Aqui você usaria sua instância configurada de whatsapp-web.js para obter as mensagens
            // $whatsapp = app('whatsapp');
            // $messages = $whatsapp->getChatById($contactId)->fetchMessages();

            // Dados de exemplo para demonstração
            $messages = [
                [
                    'id' => '101',
                    'body' => 'Olá, tudo bem?',
                    'timestamp' => now()->subMinutes(30),
                    'fromMe' => false
                ],
                [
                    'id' => '102',
                    'body' => 'Tudo ótimo! E com você?',
                    'timestamp' => now()->subMinutes(28),
                    'fromMe' => true
                ],
                [
                    'id' => '103',
                    'body' => 'Estou bem também. Vamos marcar aquela reunião?',
                    'timestamp' => now()->subMinutes(25),
                    'fromMe' => false
                ],
                [
                    'id' => '104',
                    'body' => 'Claro! Que tal amanhã às 10h?',
                    'timestamp' => now()->subMinutes(22),
                    'fromMe' => true
                ],
                [
                    'id' => '105',
                    'body' => 'Perfeito! Está marcado então.',
                    'timestamp' => now()->subMinutes(20),
                    'fromMe' => false
                ]
            ];

            // Simulando diferentes conversas para diferentes contatos
            if ($contactId === '2') {
                $messages = [
                    [
                        'id' => '201',
                        'body' => 'Vamos marcar aquela reunião',
                        'timestamp' => now()->subDays(1),
                        'fromMe' => false
                    ],
                    [
                        'id' => '202',
                        'body' => 'Sim, podemos marcar. Que tal sexta-feira?',
                        'timestamp' => now()->subDays(1)->addMinutes(5),
                        'fromMe' => true
                    ]
                ];
            }

            return response()->json($messages);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar mensagens do WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível carregar as mensagens'], 500);
        }
    }

    /**
     * Enviar uma mensagem para um contato
     */
    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'contactId' => 'required|string',
                'message' => 'required|string'
            ]);

            // Aqui você usaria sua instância configurada de whatsapp-web.js para enviar a mensagem
            // $whatsapp = app('whatsapp');
            // $chat = $whatsapp->getChatById($validated['contactId']);
            // $sentMessage = $chat->sendMessage($validated['message']);

            // Resposta simulada para demonstração
            $sentMessage = [
                'id' => uniqid(),
                'body' => $validated['message'],
                'timestamp' => now(),
                'fromMe' => true,
                'status' => 'sent'
            ];

            return response()->json($sentMessage);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem do WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível enviar a mensagem'], 500);
        }
    }

    /**
     * Receber mensagens via webhook (para processamento em tempo real)
     */
    public function webhook(Request $request)
    {
        try {
            // Processa webhooks enviados pelo cliente WhatsApp
            $payload = $request->all();

            // Aqui você processaria os eventos do WhatsApp
            // e transmitiria para seus clientes via WebSockets

            Log::info('Webhook do WhatsApp recebido', $payload);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook do WhatsApp: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao processar webhook'], 500);
        }
    }
}
