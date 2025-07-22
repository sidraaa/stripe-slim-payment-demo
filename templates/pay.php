<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay with Stripe</title>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <h2>Stripe Payment Demo</h2>
    <form action="/charge" method="POST" id="payment-form">
        <input type="email" name="stripeEmail" placeholder="Email" required><br><br>
        <div id="card-element"></div><br>
        <button type="submit">Pay $10</button>
    </form>

    <script>
        const stripe = Stripe("<?= $publishableKey ?>");
        const elements = stripe.elements();
        const card = elements.create("card");
        card.mount("#card-element");

        const form = document.getElementById("payment-form");
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const {token, error} = await stripe.createToken(card);
            if (error) {
                alert(error.message);
            } else {
                const hiddenInput = document.createElement("input");
                hiddenInput.setAttribute("type", "hidden");
                hiddenInput.setAttribute("name", "stripeToken");
                hiddenInput.setAttribute("value", token.id);
                form.appendChild(hiddenInput);

                form.submit();
            }
        });
    </script>
</body>
</html>
