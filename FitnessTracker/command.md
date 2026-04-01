docker compose down -v && docker compose up -d 

curl -X POST http://localhost:5678/webhook-test/fit-inscription      -H "Content-Type: application/json"      -d '{"user_id": 2}'


curl -X POST http://localhost:5678/webhook/fit-inscription     -H "Content-Type: application/json"      -d '{"user_id": 1}'

