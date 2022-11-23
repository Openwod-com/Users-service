# Authentication Service
En mikrotjänst för att hantera autentisering av användare.

## Gymnasiearbetet
Projektet är kopplat till gymnasiearbetet Rasmus Wiktell Sundman gör.

# Routes
```
POST /users
    Body:
        email: string, email format
        password: string
        password_confirmation: string, must have same value as password
        firstName: string
        lastName: string
    Endpointen används för att skapa ett kontot. Denna endpoint kontaktar också auth-tjänsten.

GET /users
    Body:
        Inget
    Används för att hämta information om alla användare.
```
