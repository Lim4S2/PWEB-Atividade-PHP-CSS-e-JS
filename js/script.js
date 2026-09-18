const imagens = [
  "https://images.unsplash.com/photo-1520975916090-3105956dac38?q=80&w=1200",
  "https://images.unsplash.com/photo-1516820580870-3f66c730d990?q=80&w=1200",
  "https://images.unsplash.com/photo-1507838153414-b4b713384a76?q=80&w=1200"
];

let indice = 0;

function mostrarSlide() {
  const imagem = document.getElementById("carrossel-img");

  if (imagem) {
    imagem.src = imagens[indice];
  }
}

function nextSlide() {
  indice = (indice + 1) % imagens.length;
  mostrarSlide();
}

function prevSlide() {
  indice = (indice - 1 + imagens.length) % imagens.length;
  mostrarSlide();
}

document.addEventListener("DOMContentLoaded", () => {
  const next = document.getElementById("nextSlide");
  const prev = document.getElementById("prevSlide");

  if (next) next.addEventListener("click", nextSlide);
  if (prev) prev.addEventListener("click", prevSlide);

  document.querySelectorAll(".scroll-comprar").forEach((botao) => {
    botao.addEventListener("click", () => {
      document.getElementById("comprar")?.scrollIntoView({
        behavior: "smooth"
      });
    });
  });
});
