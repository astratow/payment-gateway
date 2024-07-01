# Payment Gateway Application

## API Endpoint

### Endpoint

`POST /app/example/{gateway}`

### Parameters

- `amount` (string)
- `currency` (string)
- `card_number` (string)
- `card_exp_year` (string)
- `card_exp_month` (string)
- `card_cvv` (string)

### Example Request

```bash
php bin/console app:example shift4 --amount=100 --currency=USD --card_number=4111111111111111 --card_exp_year=2025 --card_exp_month=12 --card_cvv=123

