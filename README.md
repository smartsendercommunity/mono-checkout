# mono-checkout
Інтеграція з mono checkout https://monobank.ua/checkout





## Зручна сторінка для оформлення замовлення, яка включає в себе також вибір методу доставки (самовивіз, кур'єр, нова пошта) та методу оплати (карта, оплата частинами, оплата при отримані) тепер доступна також для чат-ботів, побудованих на SmartSender





Доступні параметри тіла запиту:


- userId - ідентифікатор користувача, обов'язкове. Використовуйте значення {{ userId }}
- action - назва тригеру, який буде запущено користувачу після успішного оформлення замовлення (успішна оплата, або оформлення з післяплатою), обов'язкове
- amount - сума замовлення (лише при використанні invoice.php, обов'язкове)
- currency - Валюта замовлення (лише при використанні invoice.php, за замовчуванням UAH, доступно також USD, EUR)
- productName - Назва товару (лише при використанні invoice.php, обов'язкове)
- productImage - Зображення товару (лише при використанні invoice.php)
- productCount - Кількість товару (лише при використанні invoice.php)
- delivery - Методи доставки, доступні на сторінці оформлення у вигляді масиву: "pickup" - самовивіз (доступно тільки за умови доданих точок самовивозу в бізнес-кабінеті моно), "np_brnm" - відділення НП, "courier" - кур'єр, "np_box" - поштомат НП (за замовчуванням доступні всі, що обрано в налаштуваннях в бізнес-кабінеті моно)
- payments - Методи оплати, доступні на сторінці оформлення замовлення у вигляді масиву: "card" - оплата картою, "payment_on_delivery" - післяплата (при отримані), "part_purchase" - оплата частинами (за замовчуванням лише карта)
- partCount - Кількість частин для оплати частинами (обов'язкове для відображення оплати частинами, мінімум 3)
- merchantDeliveryPay - вкажіть true, якщо доставка за рахунок продавця
- redirectUrl - Посилання, куди буде переадресовано користувача після оформлення замовлення (якщо не вказано, використовується вказане в бізнес-кабінеті моно. Має бути присутнє обов'язково, тому рекомендовано вказати посилання на головну Вашу сторінку в бізнес-кабінеті, щоб уникати помилок)
- description - Опис замовлення



Скриншоти зразку тіла запиту:
![image](https://github.com/user-attachments/assets/3c7e0bd5-bd73-48a4-a5f0-9b0ef4cac6a6)
![image](https://github.com/user-attachments/assets/cb404e75-2c8c-4bb0-9a3a-7b5175905a3c)



Скриншот сторінки оплати:
![image width=300px](https://github.com/user-attachments/assets/dcdcc6bc-6ecd-46c5-bda7-84e4276b0d93)
![image width=300px](https://github.com/user-attachments/assets/a061ddc1-5a66-4404-bf3c-ad0c53736db1)
![image width=300px](https://github.com/user-attachments/assets/b0d1b6a0-3ba6-4ad7-b64d-6b22f9cf2d95)
![image width=300px](https://github.com/user-attachments/assets/185fb50e-20be-4747-9834-9d78a13dfc0f)



Скриншот здійсненого замовлення в бізнес-кабінеті моно:
![image width=300px](https://github.com/user-attachments/assets/ba668074-848f-4ecc-846f-883f01ec9c01)



Скриншот транзакції у користувача (покупця, при покупці через моно):
![image width=300px](https://github.com/user-attachments/assets/be797ca3-2c92-4f0b-b166-028f1edcd62c)

