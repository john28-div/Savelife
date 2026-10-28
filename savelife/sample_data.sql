-- Optional demo animals so Find A Pet is not empty. Import after savelife_schema.sql.
USE savelife;
INSERT INTO animals (name, type, age, details, status) VALUES
('Biscuit', 'Dog', '3 months', 'Playful golden puppy found near the market. Vaccinated and friendly with children.', 'Available'),
('Luna', 'Cat', '1 year', 'Calm gray cat who likes quiet homes. Already used to a litter box.', 'Available'),
('Max', 'Dog', '2 years', 'Energetic brown dog who knows basic commands. Needs a yard to run in.', 'Available'),
('Mimi', 'Cat', '4 months', 'Curious kitten, very social. Eats both wet and dry food.', 'Available');
