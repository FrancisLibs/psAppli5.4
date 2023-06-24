class Book {
  constructor(title, author, page) {
    this.title = title;
    this.author = author;
    this.page = page;
  }
}

let myBook = new Book("test de classe", "francis", 250);

console.log(myBook.author);
