from fastapi import APIRouter, WebSocket, WebSocketDisconnect
from typing import Dict, List
import json

router = APIRouter()

class ConnectionManager:
    def __init__(self):
        self.active: Dict[int, List[WebSocket]] = {}

    async def connect(self, client_id: int, ws: WebSocket):
        await ws.accept()
        if client_id not in self.active:
            self.active[client_id] = []
        self.active[client_id].append(ws)

    def disconnect(self, client_id: int, ws: WebSocket):
        if client_id in self.active:
            if ws in self.active[client_id]:
                self.active[client_id].remove(ws)
            if not self.active[client_id]:
                del self.active[client_id]

    async def broadcast(self, client_id: int, message: dict):
        if client_id not in self.active:
            return
        dead = []
        for ws in self.active[client_id]:
            try:
                await ws.send_text(json.dumps(message, default=str))
            except Exception:
                dead.append(ws)
        for ws in dead:
            self.disconnect(client_id, ws)

manager = ConnectionManager()

@router.websocket('/ws/clients/{client_id}/diary')
async def diary_websocket(ws: WebSocket, client_id: int):
    await manager.connect(client_id, ws)
    try:
        while True:
            await ws.receive_text()
    except Exception:
        manager.disconnect(client_id, ws)