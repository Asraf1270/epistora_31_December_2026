import random

print("🎯 Welcome to the Number Guessing Game!")
print("I'm thinking of a number between 1 and 20...")

secret_number = random.randint(1, 20)
attempts = 0

while True:
    guess = int(input("Take a guess: "))
    attempts += 1

    if guess < secret_number:
        print("Too low! 📉 Try again.")
    elif guess > secret_number:
        print("Too high! 📈 Try again.")
    else:
        print(f"🎉 You got it! The number was {secret_number}.")
        print(f"You guessed it in {attempts} tries!")
        break
