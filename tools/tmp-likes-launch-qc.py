from pathlib import Path
import subprocess


def show(path: str) -> str:
    return subprocess.check_output(['git', 'show', f'origin/fix/design-foundation-compatibility:{path}'], text=True)

for rel in ['core-blueprint-likes.php','src/Admin/Assets.php','src/Admin/CoreBlueprintPage.php','src/Integration/CoreBlueprint.php','src/Plugin.php']:
    Path(rel).write_text(show(rel))
Path('src/Admin/FallbackPage.php').unlink(missing_ok=True)
print('LIKES_LAUNCH_PATCH_OK')
