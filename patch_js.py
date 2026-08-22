import os

with open('public_html/js/sesion.js', 'r', encoding='utf-8') as f:
    content = f.read()

old_tr = """        <tr style="background: ${bg}; border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 0.75rem; text-align: left; font-weight: 500; border: 1px solid #e2e8f0;">${q.label}</td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="1" onchange="updateScoringTotal()" required></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="2" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="3" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="4" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="5" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;" id="val_${q.id}">0</td>
        </tr>"""

new_tr = """        <tr style="background: ${index % 2 === 0 ? 'rgba(255,255,255,0.02)' : 'transparent'}; border-bottom: 1px solid var(--border);">
            <td style="padding: 0.75rem; text-align: left; font-weight: 500;">${q.label}</td>
            <td><input type="radio" name="${q.id}" value="1" onchange="updateScoringTotal()" required></td>
            <td><input type="radio" name="${q.id}" value="2" onchange="updateScoringTotal()"></td>
            <td><input type="radio" name="${q.id}" value="3" onchange="updateScoringTotal()"></td>
            <td><input type="radio" name="${q.id}" value="4" onchange="updateScoringTotal()"></td>
            <td><input type="radio" name="${q.id}" value="5" onchange="updateScoringTotal()"></td>
            <td style="font-weight: bold; color: var(--primary);" id="val_${q.id}">0</td>
        </tr>"""

content = content.replace(old_tr, new_tr)
with open('public_html/js/sesion.js', 'w', encoding='utf-8') as f:
    f.write(content)
